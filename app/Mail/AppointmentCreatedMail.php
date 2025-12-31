<?php

namespace App\Mail;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Appointment $appointment;
    public User $crew;
    public Department $department;

    public function __construct(
        Appointment $appointment,
        User $crew,
        Department $department
    ) {
        $this->appointment = $appointment;
        $this->crew = $crew;
        $this->department = $department;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'NYK-FIL: New Appointment Created',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment-created',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
