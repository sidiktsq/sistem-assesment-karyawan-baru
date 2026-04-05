<?php

namespace App\Livewire\Exam;

use Livewire\Component;
use App\Models\CandidateAssessment;

class ExamResult extends Component
{
    public $assignment;
    public $token;

    public function mount(CandidateAssessment $assignment, $token)
    {
        $this->assignment = $assignment;
        $this->token = $token;

        if ($this->assignment->status !== 'completed' && $this->assignment->status !== 'reviewed') {
            return redirect()->route('exam.welcome', ['token' => $this->token]);
        }
    }

    public function render()
    {
        return view('livewire.exam.result_component');
    }
}
