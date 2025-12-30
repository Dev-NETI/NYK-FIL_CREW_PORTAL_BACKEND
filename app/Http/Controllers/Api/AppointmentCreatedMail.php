<?php

namespace App\Mail;

use App\Models\Appointment;
use App\Models\Program;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $appointment;
    public $crew;
    public $department;

    public function __construct(Appointment $appointment, ?User $crew = null, ?Program $department = null)
    {
        $this->appointment = $appointment;
        $this->crew = $crew;
        $this->department = $department;
    }

    public function build()
    {
        return $this->subject('Appointment Created')
            ->view('emails.appointment-created')
            ->with([
                'appointment' => $this->appointment,
                'crew' => $this->crew,
                'department' => $this->department,
            ]);
    }
}
