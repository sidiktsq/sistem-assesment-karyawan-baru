<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected static function booted()
    {
        static::creating(function ($assessment) {
            if (auth()->check() && !$assessment->created_by) {
                $assessment->created_by = auth()->id();
            }
        });
    }

    protected $fillable = [
        'title',
        'description',
        'duration_minutes',
        'passing_score',
        'type',
        'sections',
        'is_active',
        'shuffle_questions',
        'show_result_immediately',
        'max_attempts',
        'created_by',
    ];

    protected $casts = [
        'sections' => 'json',
        'is_active' => 'boolean',
        'shuffle_questions' => 'boolean',
        'show_result_immediately' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function candidateAssessments()
    {
        return $this->hasMany(CandidateAssessment::class);
    }
}
