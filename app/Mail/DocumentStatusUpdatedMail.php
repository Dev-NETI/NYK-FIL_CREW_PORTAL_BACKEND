<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $crewName;
    public string $documentType;
    public string $documentCategory; // 'Travel', 'Employment', 'Certificate'
    public string $status; // 'approved' or 'rejected'
    public string $reviewerName;
    public ?string $rejectionReason;
    public array $documentDetails;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $crewName,
        string $documentType,
        string $documentCategory,
        string $status,
        string $reviewerName,
        array $documentDetails = [],
        ?string $rejectionReason = null
    ) {
        $this->crewName = $crewName;
        $this->documentType = $documentType;
        $this->documentCategory = $documentCategory;
        $this->status = $status;
        $this->reviewerName = $reviewerName;
        $this->documentDetails = $documentDetails;
        $this->rejectionReason = $rejectionReason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $statusText = $this->status === 'approved' ? 'Approved' : 'Rejected';

        return new Envelope(
            subject: "NYK-FIL: Document {$statusText} - {$this->documentCategory}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.document-status-updated',
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
