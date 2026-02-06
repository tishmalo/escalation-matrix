<?php

namespace Tishmalo\EscalationMatrix\Contracts;

interface SupportTicketDriver
{
    /**
     * Create a ticket in the external support system.
     *
     * @param string $subject
     * @param string $description
     * @param string $priority
     * @param array $errorData
     * @return string|null The ticket number/ID
     */
    public function createTicket(string $subject, string $description, string $priority, array $errorData): ?string;

    /**
     * Update the status of a ticket.
     *
     * @param string|int $ticketId
     * @param string $status
     * @return bool
     */
    public function updateTicketStatus($ticketId, string $status): bool;

    /**
     * Get the system user responsible for creating tickets.
     *
     * @return object|null
     */
    public function getSystemUser(): ?object;
}
