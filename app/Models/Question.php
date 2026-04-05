<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'assessment_id',
        'type',
        'section',
        'question_text',
        'options',
        'correct_answer',
        'expected_answer',
        'true_option',
        'false_option',
        'essay_guidelines',
        'min_words',
        'max_words',
        'score',
        'difficulty',
        'explanation',
        'tags',
        'order',
        'is_active',
    ];

    protected $casts = [
        'options' => 'json',
        'tags' => 'json',
        'is_active' => 'boolean',
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
