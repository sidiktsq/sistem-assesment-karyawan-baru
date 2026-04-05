<?php

namespace App\Http\Controllers;

use App\Models\CandidateAssessment;
use App\Services\InvitationService;
use App\Services\AssessmentGradingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExamController extends Controller
{
    public function __construct(
        private InvitationService $invitationService,
        private AssessmentGradingService $gradingService
    ) {}

    public function start(string $token)
    {
        $candidateAssessment = $this->invitationService->validateToken($token);
        
        if (!$candidateAssessment) {
            abort(404, 'Token tidak valid atau sudah kadaluarsa');
        }

        if ($candidateAssessment->status === 'completed') {
            return redirect()->route('exam.result', $token);
        }

        return view('exam.welcome', [
            'assignment' => $candidateAssessment,
            'token' => $token
        ]);
    }

    public function take(string $token)
    {
        $candidateAssessment = $this->invitationService->validateToken($token);
        
        if (!$candidateAssessment) {
            abort(404, 'Token tidak valid atau sudah kadaluarsa');
        }

        if ($candidateAssessment->status !== 'ongoing' && $candidateAssessment->status !== 'scheduled') {
            abort(403, 'Assessment tidak dapat diakses');
        }

        // Update status to ongoing if not already started
        if ($candidateAssessment->status === 'scheduled') {
            $candidateAssessment->update([
                'status' => 'ongoing',
                'started_at' => now()
            ]);
        }

        return view('exam.take', [
            'assignment' => $candidateAssessment,
            'token' => $token
        ]);
    }

    public function submit(Request $request, string $token): JsonResponse
    {
        $candidateAssessment = $this->invitationService->validateToken($token);
        
        if (!$candidateAssessment) {
            return response()->json(['error' => 'Token tidak valid'], 404);
        }

        $answers = $request->input('answers', []);
        
        foreach ($answers as $questionId => $answer) {
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
    }

    public function result(string $token)
    {
        $candidateAssessment = $this->invitationService->validateToken($token);
        
        if (!$candidateAssessment) {
            abort(404, 'Token tidak valid atau sudah kadaluarsa');
        }

        if (!$candidateAssessment->assessment->show_result_immediately && $candidateAssessment->status !== 'reviewed') {
            abort(403, 'Hasil belum tersedia');
        }

        return view('exam.result', [
            'assignment' => $candidateAssessment,
            'token' => $token
        ]);
    }

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
            'assessment' => $candidateAssessment->assessment,
            'candidate' => $candidateAssessment->candidate,
            'duration' => $candidateAssessment->assessment->duration_minutes,
            'time_remaining' => $this->calculateTimeRemaining($candidateAssessment)
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
