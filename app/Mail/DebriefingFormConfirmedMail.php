<?php

namespace App\Mail;

use App\Models\DebriefingForm;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DebriefingFormConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public DebriefingForm $form;
    public User $crew;
    public string $downloadUrl;

    public function __construct(DebriefingForm $form, User $crew, string $downloadUrl)
    {
        $this->form = $form;
        $this->crew = $crew;
        $this->downloadUrl = $downloadUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'NYK-FIL: Debriefing Form Confirmed',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.debriefing-form-confirmed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
