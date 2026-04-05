<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\CandidateAssessment;
use App\Models\Question;

class AssessmentGradingService
{
    public function gradeMultipleChoice(CandidateAssessment $candidateAssessment): array
    {
        $answers = Answer::where('candidate_assessment_id', $candidateAssessment->id)
            ->whereHas('question', function ($query) {
                $query->where('type', 'multiple_choice');
            })
            ->with('question')
            ->get();

        $totalQuestions = $answers->count();
        $correctAnswers = 0;
        $totalScore = 0;

        foreach ($answers as $answer) {
            if ($answer->answer === $answer->question->correct_answer) {
                $correctAnswers++;
                $totalScore += $answer->question->points ?? 1;
            }
        }

        $percentage = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;

        return [
            'total_score' => $totalScore,
            'percentage' => round($percentage, 2),
            'correct_answers' => $correctAnswers,
            'total_questions' => $totalQuestions,
            'result' => $percentage >= $candidateAssessment->assessment->passing_score ? 'pass' : 'fail'
        ];
    }

    public function gradePersonality(CandidateAssessment $candidateAssessment): array
    {
        $answers = Answer::where('candidate_assessment_id', $candidateAssessment->id)
            ->whereHas('question', function ($query) {
                $query->where('type', 'personality');
            })
            ->with('question')
            ->get();

        // For personality tests, we calculate average Likert scale score
        $totalScore = 0;
        $totalQuestions = $answers->count();

        foreach ($answers as $answer) {
            // Assuming Likert scale 1-5
            $totalScore += (int) $answer->answer;
        }

        $averageScore = $totalQuestions > 0 ? $totalScore / $totalQuestions : 0;
        $percentage = ($averageScore / 5) * 100; // Convert to percentage

        return [
            'total_score' => $totalScore,
            'percentage' => round($percentage, 2),
            'average_score' => round($averageScore, 2),
            'total_questions' => $totalQuestions,
            'result' => 'pending' // Personality tests need manual review
        ];
    }

    public function autoGrade(CandidateAssessment $candidateAssessment): void
    {
        $multipleChoiceResult = $this->gradeMultipleChoice($candidateAssessment);
        $personalityResult = $this->gradePersonality($candidateAssessment);

        // Combine results (you can adjust the weighting as needed)
        $finalScore = $multipleChoiceResult['total_score'] + $personalityResult['total_score'];
        $finalPercentage = ($multipleChoiceResult['percentage'] + $personalityResult['percentage']) / 2;

        $candidateAssessment->update([
            'total_score' => $finalScore,
            'percentage' => $finalPercentage,
            'result' => $finalPercentage >= $candidateAssessment->assessment->passing_score ? 'pass' : 'fail'
        ]);
    }
}
