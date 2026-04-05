<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class CandidateAssessment extends Model
{
    protected static function booted()
    {
        static::creating(function ($candidateAssessment) {
            if (auth()->check() && !$candidateAssessment->assigned_by) {
                $candidateAssessment->assigned_by = auth()->id();
            }

            if (!$candidateAssessment->access_token) {
                $candidateAssessment->access_token = \Illuminate\Support\Str::random(32);
            }
        });

        static::updated(function ($candidateAssessment) {
            if ($candidateAssessment->isDirty('status')) {
                $candidateStatus = match ($candidateAssessment->status) {
                    'scheduled' => 'assessment_scheduled',
                    'ongoing' => 'assessment_ongoing',
                    'completed' => 'assessment_completed',
                    'reviewed' => 'reviewed',
                    'expired' => 'assessment_expired',
                    default => null,
                };

                if ($candidateStatus) {
                    $candidateAssessment->candidate->update(['status' => $candidateStatus]);
                }
            }
        });

        // Auto check expiry during retrieval
        static::retrieved(function ($candidateAssessment) {
            if ($candidateAssessment->status === 'scheduled' && $candidateAssessment->deadline->isPast()) {
                $candidateAssessment->update(['status' => 'expired']);
            }
        });
    }

    protected $fillable = [
        'candidate_id',
        'assessment_id',
        'assigned_by',
        'scheduled_at',
        'deadline',
        'started_at',
        'completed_at',
        'status',
        'total_score',
        'max_score',
        'percentage',
        'result',
        'access_token',
        'token_expires_at',
        'result_sent_at',
        'metadata',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'deadline' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'token_expires_at' => 'datetime',
        'result_sent_at' => 'datetime',
        'metadata' => 'json',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function invitation()
    {
        return $this->hasOne(Invitation::class);
    }

    public function recalculateTotalScore()
    {
        $totalScore = 0;
        $maxScore = $this->assessment->questions->sum('score');
        
        foreach ($this->assessment->questions as $question) {
            $answer = $this->answers()->where('question_id', $question->id)->first();
            
            if ($answer) {
                if ($question->type === 'multiple_choice') {
                    if ($answer->answer === $question->correct_answer) {
                        $totalScore += $question->score;
                    }
                } elseif ($question->type === 'personality') {
                    // Personality adds the full score if answered
                    $totalScore += $question->score;
                } elseif (in_array($question->type, ['essay', 'short_answer'])) {
                    $totalScore += ($answer->score_obtained ?? 0);
                }
            }
        }
        
        $percentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 2) : 0;
        $passingScore = $this->assessment->passing_score ?? 70;
        $passed = $percentage >= $passingScore;
        
        // Check if all manual grading is done
        $hasUnreviewed = $this->answers()
            ->whereHas('question', function($q) {
                $q->whereIn('type', ['essay', 'short_answer']);
            })
            ->whereNull('reviewed_at')
            ->exists();

        $status = $this->status;
        if ($this->status === 'completed' && !$hasUnreviewed) {
            $status = 'reviewed';
        }

        $this->update([
            'total_score' => $totalScore,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'status' => $status,
            'result' => ($status === 'completed') ? 'pending' : ($passed ? 'pass' : 'fail')
        ]);
    }

    public function sendResultEmail()
    {
        if ($this->status !== 'reviewed') {
            throw new \Exception("Assessment must be in 'reviewed' status before sending results.");
        }

        Mail::to($this->candidate->email)
            ->send(new \App\Mail\AssessmentResultNotification($this));

        $this->update(['result_sent_at' => Carbon::now()]);
    }
}
