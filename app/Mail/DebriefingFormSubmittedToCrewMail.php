<?php

namespace App\Mail;

use App\Models\DebriefingForm;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DebriefingFormSubmittedToCrewMail extends Mailable
{
    use Queueable, SerializesModels;

    public DebriefingForm $form;
    public User $crew;

    public function __construct(DebriefingForm $form, User $crew)
    {
        $this->form = $form;
        $this->crew = $crew;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'NYK-FIL: Debriefing Form Submitted',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.debriefing-form-submitted-crew',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
