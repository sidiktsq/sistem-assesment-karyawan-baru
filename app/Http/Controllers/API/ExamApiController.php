<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CandidateAssessment;
use App\Services\InvitationService;
use App\Services\AssessmentGradingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExamApiController extends Controller
{
    public function __construct(
        private InvitationService $invitationService,
        private AssessmentGradingService $gradingService
    ) {}

    public function getQuestions(string $token): JsonResponse
    {
        $candidateAssessment = $this->invitationService->validateToken($token);
        
        if (!$candidateAssessment) {
            return response()->json(['error' => 'Token tidak valid'], 404);
        }

        $questions = $candidateAssessment->assessment->questions()
            ->with(['answers' => function ($query) use ($candidateAssessment) {
                $query->where('candidate_assessment_id', $candidateAssessment->id);
            }])
            ->get()
            ->map(function ($question) {
                $questionData = [
                    'id' => $question->id,
                    'type' => $question->type,
                    'question' => $question->question,
                    'points' => $question->points,
                    'options' => $question->options,
                    'time_limit' => $question->time_limit
                ];

                // Don't send correct answer for multiple choice
                if ($question->type === 'multiple_choice') {
                    unset($questionData['correct_answer']);
                }

                // Include existing answer if any
                if ($question->answers->isNotEmpty()) {
                    $questionData['user_answer'] = $question->answers->first()->answer;
                }

                return $questionData;
            });

        return response()->json([
            'questions' => $questions,
            'duration' => $candidateAssessment->assessment->duration_minutes,
            'time_remaining' => $this->calculateTimeRemaining($candidateAssessment),
            'assessment' => [
                'title' => $candidateAssessment->assessment->title,
                'type' => $candidateAssessment->assessment->type,
                'passing_score' => $candidateAssessment->assessment->passing_score
            ]
        ]);
    }

    public function saveAnswer(Request $request, string $token): JsonResponse
    {
        $candidateAssessment = $this->invitationService->validateToken($token);
        
        if (!$candidateAssessment) {
            return response()->json(['error' => 'Token tidak valid'], 404);
        }

        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'answer' => 'required|string'
        ]);

        try {
            $candidateAssessment->answers()->updateOrCreate(
                ['question_id' => $validated['question_id']],
                ['answer' => $validated['answer']]
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menyimpan jawaban'], 500);
        }
    }

    public function saveAnswers(Request $request, string $token): JsonResponse
    {
        $candidateAssessment = $this->invitationService->validateToken($token);
        
        if (!$candidateAssessment) {
            return response()->json(['error' => 'Token tidak valid'], 404);
        }

        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'string'
        ]);

        try {
            foreach ($validated['answers'] as $questionId => $answer) {
                $candidateAssessment->answers()->updateOrCreate(
                    ['question_id' => $questionId],
                    ['answer' => $answer]
                );
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menyimpan jawaban'], 500);
        }
    }

    public function submit(Request $request, string $token): JsonResponse
    {
        $candidateAssessment = $this->invitationService->validateToken($token);
        
        if (!$candidateAssessment) {
            return response()->json(['error' => 'Token tidak valid'], 404);
        }

        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'string'
        ]);

        try {
            // Save all answers
            foreach ($validated['answers'] as $questionId => $answer) {
                $candidateAssessment->answers()->updateOrCreate(
                    ['question_id' => $questionId],
                    ['answer' => $answer]
                );
            }

            // Auto-grade multiple choice and personality questions
            $this->gradingService->autoGrade($candidateAssessment);

            $candidateAssessment->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Assessment berhasil disubmit',
                'result_url' => route('exam.result', $token)
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal submit assessment'], 500);
        }
    }

    public function getResult(string $token): JsonResponse
    {
        $candidateAssessment = $this->invitationService->validateToken($token);
        
        if (!$candidateAssessment) {
            return response()->json(['error' => 'Token tidak valid'], 404);
        }

        if (!$candidateAssessment->assessment->show_result_immediately && $candidateAssessment->status !== 'reviewed') {
            return response()->json(['error' => 'Hasil belum tersedia'], 403);
        }

        return response()->json([
            'result' => [
                'total_score' => $candidateAssessment->total_score,
                'percentage' => $candidateAssessment->percentage,
                'result' => $candidateAssessment->result,
                'status' => $candidateAssessment->status
            ],
            'assessment' => $candidateAssessment
        ]);
    }

    private function calculateTimeRemaining(CandidateAssessment $candidateAssessment): int
    {
        if (!$candidateAssessment->started_at) {
            return $candidateAssessment->assessment->duration_minutes * 60;
        }

        $elapsed = now()->diffInSeconds($candidateAssessment->started_at);
        $totalTime = $candidateAssessment->assessment->duration_minutes * 60;
        
        return max(0, $totalTime - $elapsed);
    }
}
