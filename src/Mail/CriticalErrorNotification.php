<?php

namespace Tishmalo\EscalationMatrix\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CriticalErrorNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $errorData;
    public $contact;
    public $priority;

    /**
     * Create a new message instance.
     */
    public function __construct(array $errorData, array $contact, string $priority)
    {
        $this->errorData = $errorData;
        $this->contact = $contact;
        $this->priority = $priority;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $priorityEmoji = $this->getPriorityEmoji($this->priority);
        $subject = "{$priorityEmoji} [{$this->errorData['priority_label']}] {$this->errorData['exception']['type_short']}";

        return $this->subject($subject)
                    ->view('escalation-matrix::emails.error-notification')
                    ->with([
                        'errorData' => $this->errorData,
                        'contact' => $this->contact,
                        'priority' => $this->priority,
                    ]);
    }

    /**
     * Get emoji for priority level
     */
    private function getPriorityEmoji(string $priority): string
    {
        $emojis = [
            'critical' => '🚨',
            'high' => '⚠️',
            'medium' => 'ℹ️',
            'low' => '📝',
        ];

        return $emojis[$priority] ?? '⚠️';
    }
}
