<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ExamApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Debug Route
Route::get('/debug', function () {
    return response()->json(['message' => 'API is working!', 'timestamp' => now()]);
});

// Simple Exam API
Route::prefix('exam')->group(function () {
    Route::get('/{token}/questions', function ($token) {
        $ca = \App\Models\CandidateAssessment::where('access_token', $token)->first();
        if (!$ca) {
            return response()->json(['error' => 'Token tidak valid'], 404);
        }
        
        // Check if assessment is already completed
        if ($ca->status === 'completed' || $ca->status === 'reviewed') {
            return response()->json(['error' => 'Assessment sudah selesai'], 403);
        }
        
        // Update status to ongoing if not already
        if ($ca->status === 'scheduled' || $ca->status === 'completed') {
            $ca->update(['status' => 'ongoing', 'started_at' => now()]);
        }
        
        $questions = $ca->assessment->questions()->get()->map(function ($q) use ($ca) {
            // Get user's existing answer if any
            $existingAnswer = \App\Models\Answer::where('candidate_assessment_id', $ca->id)
                ->where('question_id', $q->id)
                ->first();
            
            return [
                'id' => $q->id,
                'type' => $q->type,
                'question' => $q->question_text,
                'options' => $q->options,
                'score' => $q->score,
                'user_answer' => $existingAnswer?->answer
            ];
        });
        
        // Calculate time remaining
        $timeRemaining = 0;
        if ($ca->started_at) {
            $durationSeconds = $ca->assessment->duration_minutes * 60;
            $elapsedSeconds = max(0, now()->diffInSeconds($ca->started_at));
            $timeRemaining = max(0, $durationSeconds - $elapsedSeconds);
            
            // Debug log
            error_log("Time calculation - Duration: {$durationSeconds}, Elapsed: {$elapsedSeconds}, Remaining: {$timeRemaining}");
        }
        
        return response()->json([
            'questions' => $questions,
            'assessment' => [
                'title' => $ca->assessment->title,
                'description' => $ca->assessment->description,
                'duration_minutes' => $ca->assessment->duration_minutes,
                'type' => $ca->assessment->type,
                'passing_score' => $ca->assessment->passing_score,
            ],
            'candidate' => [
                'name' => $ca->candidate->name,
                'email' => $ca->candidate->email,
            ],
            'time_remaining' => $timeRemaining,
        ]);
    });
    
    Route::post('/{token}/save-answer', function ($token, Request $request) {
        $ca = \App\Models\CandidateAssessment::where('access_token', $token)->first();
        if (!$ca) {
            return response()->json(['error' => 'Token tidak valid'], 404);
        }
        
        if ($ca->status !== 'ongoing') {
            return response()->json(['error' => 'Assessment tidak dalam progress'], 403);
        }
        
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'answer' => 'required|string'
        ]);
        
        // Save or update answer
        \App\Models\Answer::updateOrCreate(
            [
                'candidate_assessment_id' => $ca->id,
                'question_id' => $validated['question_id']
            ],
            [
                'answer' => $validated['answer'],
                'answered_at' => now()
            ]
        );
        
        return response()->json(['success' => true]);
    });
    
    Route::post('/{token}/save-answers', function ($token, Request $request) {
        $ca = \App\Models\CandidateAssessment::where('access_token', $token)->first();
        if (!$ca) {
            return response()->json(['error' => 'Token tidak valid'], 404);
        }
        
        if ($ca->status !== 'ongoing') {
            return response()->json(['error' => 'Assessment tidak dalam progress'], 403);
        }
        
        $validated = $request->validate([
            'answers' => 'required|array'
        ]);
        
        // Batch save answers (map format: question_id => answer)
        foreach ($validated['answers'] as $questionId => $answer) {
            \App\Models\Answer::updateOrCreate(
                [
                    'candidate_assessment_id' => $ca->id,
                    'question_id' => $questionId
                ],
                [
                    'answer' => $answer,
                    'answered_at' => now()
                ]
            );
        }
        
        return response()->json(['success' => true]);
    });
    
    Route::post('/{token}/submit', function ($token, Request $request) {
        $ca = \App\Models\CandidateAssessment::where('access_token', $token)->first();
        if (!$ca) {
            return response()->json(['error' => 'Token tidak valid'], 404);
        }
        
        if ($ca->status !== 'ongoing') {
            return response()->json(['error' => 'Assessment tidak dalam progress'], 403);
        }
        
        $validated = $request->validate([
            'answers' => 'required|array'
        ]);
        
        // Save all final answers
        foreach ($validated['answers'] as $questionId => $answer) {
            \App\Models\Answer::updateOrCreate(
                [
                    'candidate_assessment_id' => $ca->id,
                    'question_id' => $questionId
                ],
                [
                    'answer' => $answer,
                    'answered_at' => now()
                ]
            );
        }
        
        // === AUTO-GRADING ===
        // Multiple Choice → Cocokkan dengan correct_answer
        // Personality     → Simpan skala Likert, analisis nanti (score = 0 dari sistem)
        // Essay           → Manual oleh reviewer (score = 0 dari sistem)
        $totalScore = 0;
        $maxScore = 0;
        $hasManualGrading = false; // true jika ada essay atau personality yang butuh review
        
        foreach ($ca->assessment->questions as $question) {
            $answer = \App\Models\Answer::where('candidate_assessment_id', $ca->id)
                ->where('question_id', $question->id)
                ->first();
            
            $maxScore += $question->score;
            
            if ($question->type === 'multiple_choice') {
                // ✅ Auto-grade: cocokkan jawaban dengan correct_answer
                if ($answer && $answer->answer === $question->correct_answer) {
                    $totalScore += $question->score;
                    if ($answer) {
                        $answer->update(['score_obtained' => $question->score, 'is_correct' => true]);
                    }
                } else {
                    if ($answer) {
                        $answer->update(['score_obtained' => 0, 'is_correct' => false]);
                    }
                }
            } elseif ($question->type === 'personality') {
                // ✅ Personality auto-grade: Full score if answered
                if ($answer && !empty($answer->answer)) {
                    $totalScore += $question->score;
                    $answer->update([
                        'score_obtained' => $question->score,
                        'is_correct' => true,
                        'reviewed_at' => now(),
                        'needs_review' => false
                    ]);
                }
            } elseif ($question->type === 'essay' || $question->type === 'short_answer') {
                // ✅ Manual oleh reviewer
                $hasManualGrading = true;
            }
        }
        
        // Update assessment status
        $ca->update([
            'status' => 'completed',
            'completed_at' => now(),
            'total_score' => $totalScore,
            'max_score' => $maxScore
        ]);
        
        return response()->json([
            'success' => true,
            'result_url' => url("/exam/{$token}/result")
        ]);
    });
    
    Route::get('/{token}/result', function ($token) {
        $ca = \App\Models\CandidateAssessment::where('access_token', $token)->first();
        if (!$ca) {
            return response()->json(['error' => 'Token tidak valid'], 404);
        }
        
        if ($ca->status !== 'completed' && $ca->status !== 'reviewed') {
            return response()->json(['error' => 'Assessment belum selesai'], 403);
        }
        
        // Calculate result
        $maxScore = $ca->max_score ?: $ca->assessment->questions->sum('score');
        $percentage = $maxScore > 0 ? round(($ca->total_score / $maxScore) * 100, 2) : 0;
        $passed = $percentage >= ($ca->assessment->passing_score ?? 70);
        
        // Check if there are essay questions that need manual review
        $hasEssayQuestions = $ca->assessment->questions()
            ->where('type', 'essay')
            ->exists();
        
        $isPending = ($hasEssayQuestions && $ca->status === 'completed') || $ca->status === 'ongoing';
        
        $result = [
            'result' => $isPending ? 'pending' : ($passed ? 'pass' : 'fail'),
            'percentage' => $isPending ? null : $percentage,
            'total_score' => $isPending ? null : $ca->total_score,
            'max_score' => $maxScore,
            'passed' => $isPending ? false : $passed,
            'status' => $ca->status,
            'is_pending' => $isPending,
            'result_sent_at' => $ca->result_sent_at ? $ca->result_sent_at->toIso8601String() : null,
        ];
        
        return response()->json([
            'result' => $result,
            'assessment' => [
                'title' => $ca->assessment->title,
                'type' => $ca->assessment->type,
                'duration_minutes' => $ca->assessment->duration_minutes,
                'passing_score' => $ca->assessment->passing_score,
                'questions_count' => $ca->assessment->questions->count(),
                'started_at' => $ca->started_at ? $ca->started_at->toIso8601String() : null,
                'completed_at' => $ca->completed_at ? $ca->completed_at->toIso8601String() : null
            ],
            'candidate' => [
                'name' => $ca->candidate->name,
                'email' => $ca->candidate->email,
                'position_applied' => $ca->candidate->position_applied
            ]
        ]);
    });
});

// Reviewer Routes
Route::prefix('reviewer')->group(function () {
    // Get all completed assessments for review
    Route::get('/assessments', function () {
        $assessments = \App\Models\CandidateAssessment::with(['candidate', 'assessment'])
            ->whereIn('status', ['completed', 'reviewed'])
            ->get()
            ->map(function ($ca) {
                $hasEssayQuestions = $ca->assessment->questions()
                    ->where('type', 'essay')
                    ->exists();
                
                // Calculate max_score from questions
                $maxScore = $ca->assessment->questions->sum('score');
                
                return [
                    'id' => $ca->id,
                    'candidate' => [
                        'name' => $ca->candidate->name,
                        'email' => $ca->candidate->email,
                        'position_applied' => $ca->candidate->position_applied
                    ],
                    'assessment' => [
                        'title' => $ca->assessment->title,
                        'type' => $ca->assessment->type,
                        'duration_minutes' => $ca->assessment->duration_minutes
                    ],
                    'status' => $ca->status,
                    'total_score' => $ca->total_score,
                    'max_score' => $maxScore,
                    'percentage' => $maxScore > 0 ? round(($ca->total_score / $maxScore) * 100, 2) : 0,
                    'has_essay_questions' => $hasEssayQuestions,
                    'completed_at' => $ca->completed_at,
                    'access_token' => $ca->access_token
                ];
            });
        
        return response()->json($assessments);
    });
    
    // Get assessment details for review
    Route::get('/assessment/{token}/details', function ($token) {
        $ca = \App\Models\CandidateAssessment::where('access_token', $token)
            ->with(['candidate', 'assessment.questions'])
            ->first();
        
        if (!$ca) {
            return response()->json(['error' => 'Assessment not found'], 404);
        }
        
        // Check if assessment is ready for review
        if (!in_array($ca->status, ['completed', 'reviewed'])) {
            return response()->json(['error' => 'Assessment not ready for review'], 403);
        }
        
        // Calculate max_score from questions
        $maxScore = $ca->assessment->questions->sum('score');
        
        // Get all answers with questions
        $questionsWithAnswers = $ca->assessment->questions->map(function ($question) use ($ca) {
            $answer = \App\Models\Answer::where('candidate_assessment_id', $ca->id)
                ->where('question_id', $question->id)
                ->first();
            
            return [
                'id' => $question->id,
                'type' => $question->type,
                'question_text' => $question->question_text,
                'options' => $question->options,
                'correct_answer' => $question->correct_answer,
                'score' => $question->score,
                'user_answer' => $answer?->answer,
                'answer_id' => $answer?->id,
                'is_graded' => $answer && $answer->score_obtained !== null,
                'current_score' => $answer?->score_obtained,
                'feedback' => $answer?->feedback
            ];
        });
        
        return response()->json([
            'assessment' => [
                'id' => $ca->id,
                'title' => $ca->assessment->title,
                'type' => $ca->assessment->type,
                'passing_score' => $ca->assessment->passing_score,
                'duration_minutes' => $ca->assessment->duration_minutes
            ],
            'candidate' => [
                'name' => $ca->candidate->name,
                'email' => $ca->candidate->email,
                'position_applied' => $ca->candidate->position_applied
            ],
            'status' => $ca->status,
            'total_score' => $ca->total_score ?: 0,
            'max_score' => $maxScore,
            'percentage' => $maxScore > 0 ? round(($ca->total_score / $maxScore) * 100, 2) : 0,
            'questions' => $questionsWithAnswers,
            'completed_at' => $ca->completed_at,
            'started_at' => $ca->started_at,
            'result_sent_at' => $ca->result_sent_at,
        ]);
    });
    
    // Send assessment results via email
    Route::post('/assessment/{token}/send-results', function ($token) {
        $ca = \App\Models\CandidateAssessment::where('access_token', $token)->first();
        
        if (!$ca) {
            return response()->json(['error' => 'Assessment not found'], 404);
        }
        
        if ($ca->status !== 'reviewed') {
            return response()->json(['error' => 'Assessment must be reviewed first'], 403);
        }
        
        try {
            $ca->sendResultEmail();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });
    
    // Save essay score and feedback
    Route::post('/assessment/{token}/grade', function ($token, Request $request) {
        $ca = \App\Models\CandidateAssessment::where('access_token', $token)->first();
        
        if (!$ca) {
            return response()->json(['error' => 'Assessment not found'], 404);
        }
        
        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.answer_id' => 'required|exists:answers,id',
            'answers.*.score' => 'required|integer|min:0',
            'answers.*.feedback' => 'nullable|string'
        ]);
        
        // Update scores and feedback for each answer
        foreach ($validated['answers'] as $answerData) {
            $answer = \App\Models\Answer::find($answerData['answer_id']);
            if ($answer && $answer->candidate_assessment_id === $ca->id) {
                $answer->update([
                    'score_obtained' => $answerData['score'],
                    'feedback' => $answerData['feedback'] ?? null,
                    'reviewed_at' => now()
                ]);
            }
        }
        
        // Recalculate total score
        $totalScore = 0;
        $maxScore = $ca->assessment->questions->sum('score');
        
        foreach ($ca->assessment->questions as $question) {
            $answer = \App\Models\Answer::where('candidate_assessment_id', $ca->id)
                ->where('question_id', $question->id)
                ->first();
            
            if ($answer) {
                if ($question->type === 'multiple_choice') {
                    if ($answer->answer === $question->correct_answer) {
                        $totalScore += $question->score;
                    }
                } elseif ($question->type === 'personality') {
                    $totalScore += $question->score;
                } elseif ($question->type === 'essay' || $question->type === 'short_answer') {
                    $totalScore += $answer->score_obtained ?? 0;
                }
            }
        }
        
        // Update assessment
        $percentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 2) : 0;
        $passed = $percentage >= ($ca->assessment->passing_score ?? 70);
        
        $ca->update([
            'total_score' => $totalScore,
            'percentage' => $percentage,
            'status' => 'reviewed',
            'result' => $passed ? 'pass' : 'fail'
        ]);
        
        return response()->json([
            'success' => true,
            'total_score' => $totalScore,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'result' => $passed ? 'pass' : 'fail'
        ]);
    });
});
