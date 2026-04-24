<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected static function booted()
    {
        static::saved(function ($review) {
            $candidate = $review->candidateAssessment->candidate;
            $candidateAssessment = $review->candidateAssessment;
            
            if (in_array($review->recommendation, ['approved', 'probation', 'rejected'])) {
                // Update Candidate status
                if ($candidate) {
                    $candidate->update(['status' => $review->recommendation]);
                }
                // Update CandidateAssessment status to match
                if ($candidateAssessment) {
                    $candidateAssessment->update(['status' => $review->recommendation]);
                }
            }
        });
    }

    protected $fillable = [
        'candidate_assessment_id',
        'reviewer_id',
        'recommendation',
        'notes',
        'aspect_scores',
        'reviewed_at',
    ];

    protected $casts = [
        'aspect_scores' => 'json',
        'reviewed_at' => 'datetime',
    ];

    public function candidateAssessment()
    {
        return $this->belongsTo(CandidateAssessment::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
