<?php

namespace App\Services;

use App\Models\CandidateAssessment;
use App\Models\Answer;

class GradingService
{
    /**
     * Calculate score for multiple choice questions.
     */
    public function autoGrade(CandidateAssessment $session)
    {
        $session->load('answers.question');
        
        $totalScore = 0;
        $earnedScore = 0;

        foreach ($session->answers as $answer) {
            $question = $answer->question;
            
            // Only auto-grade multiple choice
            if ($question->type === 'multiple_choice') {
                $totalScore += $question->score;
                
                if ($answer->answer === $question->correct_answer) {
                    $earnedScore += $question->score;
                    $answer->update(['is_correct' => true, 'points' => $question->score]);
                } else {
                    $answer->update(['is_correct' => false, 'points' => 0]);
                }
            } elseif ($question->type === 'essay') {
                $totalScore += $question->score;
                // Essay needs manual review, leave score 0 for now
            }
        }

        $percentage = $totalScore > 0 ? round(($earnedScore / $totalScore) * 100, 2) : 0;
        
        $session->update([
            'total_score' => $earnedScore,
            'percentage' => $percentage,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return $session;
    }
}
