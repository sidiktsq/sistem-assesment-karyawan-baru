<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = [
        'candidate_assessment_id',
        'question_id',
        'answer',
        'is_correct',
        'score_obtained',
        'feedback',
        'reviewed_by',
        'reviewed_at',
        'time_spent_seconds',
        'needs_review',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'reviewed_at' => 'datetime',
        'needs_review' => 'boolean',
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function ($answer) {
            // Jika ada perubahan pada skor, status benar, atau feedback
            // maka set reviewed_at dan reviewed_by (jika ada user login)
            if ($answer->isDirty(['score_obtained', 'is_correct', 'feedback'])) {
                $answer->reviewed_at = now();
                if (auth()->check()) {
                    $answer->reviewed_by = auth()->id();
                }
            }
        });

        static::saved(function ($answer) {
            // Setelah jawaban disimpan, hitung ulang total skor di assessment
            if ($answer->candidateAssessment) {
                $answer->candidateAssessment->recalculateTotalScore();
            }
        });
    }

    public function candidateAssessment()
    {
        return $this->belongsTo(CandidateAssessment::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
