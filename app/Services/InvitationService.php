<?php

namespace App\Services;

use App\Models\CandidateAssessment;
use App\Models\Invitation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\AssessmentInvitation;

class InvitationService
{
    public function createInvitation(CandidateAssessment $candidateAssessment): Invitation
    {
        $token = $this->generateUniqueToken();
        $tokenExpiresAt = $candidateAssessment->deadline;

        return Invitation::create([
            'candidate_assessment_id' => $candidateAssessment->id,
            'email' => $candidateAssessment->candidate->email,
            'token' => $token,
            'expires_at' => $tokenExpiresAt,
            'sent_at' => null,
        ]);
    }

    public function sendInvitation(CandidateAssessment $candidateAssessment): bool
    {
        $invitation = $this->createInvitation($candidateAssessment);
        $candidateAssessment->refresh();

        try {
            Mail::to($candidateAssessment->candidate->email)
                ->send(new AssessmentInvitation($candidateAssessment, $invitation));

            $invitation->update(['sent_at' => now()]);
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send invitation email', [
                'email' => $candidateAssessment->candidate->email,
                'candidate_id' => $candidateAssessment->candidate_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    public function sendBulkInvitations(array $candidateAssessmentIds): array
    {
        $results = [];
        
        foreach ($candidateAssessmentIds as $id) {
            $candidateAssessment = CandidateAssessment::find($id);
            if ($candidateAssessment) {
                $results[$id] = $this->sendInvitation($candidateAssessment);
            } else {
                $results[$id] = false;
            }
        }

        return $results;
    }

    private function generateUniqueToken(): string
    {
        do {
            $token = Str::random(32);
        } while (Invitation::where('token', $token)->exists());

        return $token;
    }

    public function validateToken(string $token): ?CandidateAssessment
    {
        $invitation = Invitation::with(['candidateAssessment.assessment', 'candidateAssessment.candidate'])
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation) {
            return null;
        }

        return $invitation->candidateAssessment;
    }
}
