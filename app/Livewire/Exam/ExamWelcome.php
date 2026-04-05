<?php

namespace App\Livewire\Exam;

use Livewire\Component;
use App\Models\CandidateAssessment;

class ExamWelcome extends Component
{
    public $assignment;
    public $token;

    public function mount(CandidateAssessment $assignment, $token)
    {
        $this->assignment = $assignment;
        $this->token = $token;
    }

    public function startExam()
    {
        if (!$this->assignment->started_at) {
            $this->assignment->update([
                'started_at' => now(),
                'status' => 'ongoing',
            ]);
        }

        return redirect()->route('exam.take', ['token' => $this->token]);
    }

    public function render()
    {
        return view('livewire.exam.welcome_component');
    }
}
