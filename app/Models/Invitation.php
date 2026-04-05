<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    protected $fillable = [
        'candidate_assessment_id',
        'email',
        'token',
        'sent_at',
        'expires_at',
        'accepted_at',
        'status',
        'reminder_count',
        'last_reminder_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'last_reminder_at' => 'datetime',
    ];

    public function candidateAssessment()
    {
        return $this->belongsTo(CandidateAssessment::class);
    }
}
