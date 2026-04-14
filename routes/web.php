<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ExamController;

// Debug Route
Route::get('/debug-token/{token}', function ($token) {
    $ca = \App\Models\CandidateAssessment::where('access_token', $token)->first();
    if ($ca) {
        return response()->json([
            'valid' => true,
            'candidate' => $ca->candidate->name,
            'assessment' => $ca->assessment->title,
            'status' => $ca->status,
            'questions_count' => $ca->assessment->questions->count()
        ]);
    } else {
        return response()->json(['valid' => false, 'error' => 'Token not found'], 404);
    }
});

Route::get('/', function () {
    return redirect('/admin');
});

Route::prefix('exam')->group(function () {
    Route::get('/{token}', function ($token) {
        // Validate token first
        $ca = \App\Models\CandidateAssessment::where('access_token', $token)->first();
        if (!$ca) {
            abort(404, 'Token tidak valid');
        }
        
        // Redirect to Vue.js frontend landing page (token entry)
        return redirect(config('app.frontend_url'));
    })->name('exam.start');
    
    Route::get('/{token}/take', function ($token) {
        // Validate token first
        $ca = \App\Models\CandidateAssessment::where('access_token', $token)->first();
        if (!$ca) {
            abort(404, 'Token tidak valid');
        }
        
        // Redirect to Vue.js frontend (running on different port)
        $frontendUrl = 'http://localhost:5173';
        return redirect("{$frontendUrl}/exam/{$token}/take");
    })->name('exam.take');
    
    Route::get('/{token}/result', function ($token) {
        $ca = \App\Models\CandidateAssessment::where('access_token', $token)->first();
        if (!$ca) {
            abort(404, 'Token tidak valid');
        }
        return view('exam.result', [
            'assignment' => $ca,
            'token' => $token
        ]);
    })->name('exam.result');
    
    Route::post('/{token}/submit', [ExamController::class, 'submit'])->name('exam.submit');
    Route::get('/{token}/questions', [ExamController::class, 'getQuestions'])->name('exam.questions');
});
