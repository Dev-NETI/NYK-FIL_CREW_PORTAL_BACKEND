<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminMessageNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $messageContent;
    public string $senderName;
    public string $senderEmail;
    public string $inquirySubject;
    public int $inquiryId;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $messageContent,
        string $senderName,
        string $senderEmail,
        string $inquirySubject,
        int $inquiryId
    ) {
        $this->messageContent = $messageContent;
        $this->senderName = $senderName;
        $this->senderEmail = $senderEmail;
        $this->inquirySubject = $inquirySubject;
        $this->inquiryId = $inquiryId;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Message from NYK-FIL Crew Portal - Inquiry #' . $this->inquiryId,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-message-notification',
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
