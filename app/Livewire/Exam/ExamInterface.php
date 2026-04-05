<?php

namespace App\Livewire\Exam;

use Livewire\Component;
use App\Models\CandidateAssessment;
use App\Models\Question;
use App\Models\Answer;
use Carbon\Carbon;

class ExamInterface extends Component
{
    public $assignment;
    public $token;
    public $questions;
    public $currentIndex = 0;
    public $answers = [];
    public $timeLeft; // in seconds
    public $endTime;

    public function mount(CandidateAssessment $assignment, $token)
    {
        $this->assignment = $assignment;
        $this->token = $token;

        // Ensure status is ongoing
        if ($this->assignment->status !== 'ongoing') {
            return redirect()->route('exam.welcome', ['token' => $this->token]);
        }

        // Load questions
        $this->questions = $this->assignment->assessment->questions()->orderBy('order')->get();

        // Load existing answers
        $existingAnswers = Answer::where('candidate_assessment_id', $this->assignment->id)->get();
        foreach ($existingAnswers as $answer) {
            $this->answers[$answer->question_id] = $answer->answer;
        }

        // Setup Timer
        $duration = $this->assignment->assessment->duration_minutes * 60;
        $this->endTime = $this->assignment->started_at->addSeconds($duration);
        $this->calculateTimeLeft();
    }

    public function calculateTimeLeft()
    {
        $now = now();
        if ($now->greaterThan($this->endTime)) {
            $this->timeLeft = 0;
            $this->finishExam();
        } else {
            $this->timeLeft = $this->endTime->diffInSeconds($now);
        }
    }

    public function goToQuestion($index)
    {
        if ($index >= 0 && $index < $this->questions->count()) {
            $this->currentIndex = $index;
        }
    }

    public function saveAnswer($questionId, $value)
    {
        $this->answers[$questionId] = $value;

        $isEssay = $this->questions->firstWhere('id', $questionId)->type === 'essay';

        Answer::updateOrCreate(
            [
                'candidate_assessment_id' => $this->assignment->id,
                'question_id' => $questionId,
            ],
            [
                'answer' => $value,
                'needs_review' => $isEssay,
            ]
        );
    }

    public function finishExam()
    {
        $this->assignment->update([
            'completed_at' => now(),
            'status' => 'completed',
        ]);

        return redirect()->route('exam.result', ['token' => $this->token]);
    }

    public function render()
    {
        $this->calculateTimeLeft();
        return view('livewire.exam.interface_component', [
            'currentQuestion' => $this->questions[$this->currentIndex] ?? null,
        ]);
    }
}
