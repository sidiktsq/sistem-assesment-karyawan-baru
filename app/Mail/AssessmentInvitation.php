<?php

namespace App\Mail;

use App\Models\CandidateAssessment;
use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AssessmentInvitation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public CandidateAssessment $candidateAssessment,
        public Invitation $invitation
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Undangan Assessment Online - ' . $this->candidateAssessment->assessment->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.assessment-invitation',
            with: [
                'candidate'   => $this->candidateAssessment->candidate,
                'assessment'  => $this->candidateAssessment->assessment,
                'scheduledAt' => $this->candidateAssessment->scheduled_at,
                'deadline'    => $this->candidateAssessment->deadline,
                'examUrl'     => config('app.frontend_url') . '/start/' . $this->candidateAssessment->access_token,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
