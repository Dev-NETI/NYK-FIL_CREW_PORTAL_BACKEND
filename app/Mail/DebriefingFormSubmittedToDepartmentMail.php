<?php

namespace App\Mail;

use App\Models\DebriefingForm;
use App\Models\Department;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DebriefingFormSubmittedToDepartmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public DebriefingForm $form;
    public User $crew;
    public Department $department;

    public function __construct(DebriefingForm $form, User $crew, Department $department)
    {
        $this->form = $form;
        $this->crew = $crew;
        $this->department = $department;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'NYK-FIL: New Debriefing Form Submitted',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.debriefing-form-submitted-department',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
