<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'email',
        'phone',
        'position_applied',
        'source',
        'status',
        'notes',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assessments()
    {
        return $this->hasMany(CandidateAssessment::class);
    }
}
