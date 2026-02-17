<?php

namespace App\Mail;

use App\Models\Appointment;
use App\Models\AppointmentCancellation;
use App\Models\Department;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public Appointment $appointment;
    public AppointmentCancellation $cancellation;
    public User $crew;
    public Department $department;

    public function __construct(
        Appointment $appointment,
        AppointmentCancellation $cancellation,
        User $crew,
        Department $department
    ) {
        $this->appointment = $appointment;
        $this->cancellation = $cancellation;
        $this->crew = $crew;
        $this->department = $department;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'NYK-FIL: Appointment Cancelled',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment-cancelled',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
