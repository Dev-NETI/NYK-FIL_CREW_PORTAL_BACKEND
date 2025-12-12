<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentSubmittedToAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $crewName;
    public string $crewId;
    public string $documentType;
    public string $documentCategory; // 'Travel', 'Employment', 'Certificate'
    public string $action; // 'created' or 'updated'
    public array $documentDetails;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $crewName,
        string $crewId,
        string $documentType,
        string $documentCategory,
        string $action,
        array $documentDetails = []
    ) {
        $this->crewName = $crewName;
        $this->crewId = $crewId;
        $this->documentType = $documentType;
        $this->documentCategory = $documentCategory;
        $this->action = $action;
        $this->documentDetails = $documentDetails;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $actionText = $this->action === 'created' ? 'New Document Submission' : 'Document Update Request';

        return new Envelope(
            subject: "NYK-FIL: {$actionText} - {$this->documentCategory} Document",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.document-submitted-to-admin',
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
