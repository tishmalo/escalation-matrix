<?php

namespace Tishmalo\EscalationMatrix\Drivers;

use Tishmalo\EscalationMatrix\Contracts\SupportTicketDriver;
use Tishmalo\EscalationMatrix\Models\PackageTicket;

class LocalTicketDriver implements SupportTicketDriver
{
    /**
     * Create a ticket in the local database.
     *
     * @param string $subject
     * @param string $description
     * @param string $priority
     * @param array $errorData
     * @return string|null The ticket ID
     */
    public function createTicket(string $subject, string $description, string $priority, array $errorData): ?string
    {
        try {
            $ticket = PackageTicket::create([
                'subject' => $subject,
                'description' => $description,
                'priority' => $priority,
                'status' => 'open',
                'metadata' => $errorData,
            ]);

            return (string) $ticket->id;
        } catch (\Exception $e) {
            \Log::error('Failed to create local ticket: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the system user responsible for creating tickets.
     *
     * @return object|null
     */
    public function getSystemUser(): ?object
    {
        return null;
    }
}
