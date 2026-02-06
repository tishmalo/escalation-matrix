<?php

namespace Tishmalo\EscalationMatrix\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CriticalErrorNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public array $errorData,
        public array $contact,
        public string $priority
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $priorityEmoji = match ($this->priority) {
            'critical' => '🚨',
            'high' => '⚠️',
            'medium' => 'ℹ️',
            'low' => '📝',
            default => '⚠️',
        };

        // Use array access for errorData as passed from service
        $subject = "{$priorityEmoji} [{$this->errorData['priority_label']}] {$this->errorData['exception']['type_short']}";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'escalation-matrix::emails.error-notification',
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
