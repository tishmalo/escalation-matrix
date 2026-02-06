<?php

namespace Tishmalo\EscalationMatrix\Services;

use Tishmalo\EscalationMatrix\Contracts\SupportTicketDriver;
use Tishmalo\EscalationMatrix\Mail\CriticalErrorNotification; // Assuming we create this
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EscalationService
{
    public function __construct(
        protected SupportTicketDriver $ticketDriver,
        protected SmsService $smsService
    ) {
    }

    /**
     * Handle exception notification and ticket creation
     */
    public function handle(Throwable $exception): void
    {
        // Check if notifications are enabled
        if (!config('escalation.enabled', true)) {
            return;
        }

        // Check if this exception should be notified
        if (!$this->shouldNotify($exception)) {
            Log::debug('Exception ignored by escalation policy', [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
            ]);
            return;
        }

        // Determine priority level
        $priority = $this->determinePriority($exception);

        // Check rate limiting
        if (!$this->checkRateLimit($exception, $priority)) {
            Log::info('Exception notification rate limited', [
                'exception' => get_class($exception),
                'priority' => $priority,
            ]);
            return;
        }

        // Format error data
        $errorData = $this->formatErrorData($exception, $priority);

        // Create ticket if enabled
        $ticketId = null;
        if (config('escalation.ticket_enabled', true)) {
            $ticketId = $this->createTicket($errorData);
            $errorData['ticket_number'] = $ticketId;
        }

        // Get notification channels for this priority
        $channels = config("escalation.notification_channels.{$priority}", ['ticket']);

        // Get escalation contacts
        $contacts = $this->getEscalationContacts($priority);

        // Send notifications
        if (!empty($contacts)) {
            $this->notifyContacts($contacts, $errorData, $priority, $channels);
        }

        // Record notification to prevent spam
        $this->recordNotification($exception, $priority);

        Log::info('Exception notification sent', [
            'exception' => get_class($exception),
            'priority' => $priority,
            'ticket_number' => $ticketId,
            'contacts_notified' => count($contacts),
        ]);
    }

    /**
     * Determine if exception should trigger notifications
     */
    protected function shouldNotify(Throwable $exception): bool
    {
        $ignoredExceptions = config('escalation.ignored_exceptions', []);

        foreach ($ignoredExceptions as $ignoredException) {
            if ($exception instanceof $ignoredException) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine priority level based on exception type
     */
    protected function determinePriority(Throwable $exception): string
    {
        $mapping = config('escalation.exception_mapping', []);

        // Check each priority level
        foreach (['critical', 'high', 'medium', 'low'] as $priority) {
            $exceptions = $mapping[$priority] ?? [];

            foreach ($exceptions as $exceptionClass) {
                if ($exception instanceof $exceptionClass) {
                    return $priority;
                }
            }
        }

        // Default to medium if no match
        return 'medium';
    }

    /**
     * Get escalation contacts for priority level
     */
    protected function getEscalationContacts(string $priority): array
    {
        return config("escalation.escalation_matrix.{$priority}", []);
    }

    /**
     * Send notifications to contacts
     */
    protected function notifyContacts(array $contacts, array $errorData, string $priority, array $channels): void
    {
        foreach ($contacts as $contact) {
            // Prioritize Email: Send email if channel is enabled
            if (in_array('email', $channels) && !empty($contact['email'])) {
                try {
                    // Start check for Mailable existence to avoid error during dev
                    if (class_exists(\Tishmalo\EscalationMatrix\Mail\CriticalErrorNotification::class)) {
                         Mail::to($contact['email'])
                            ->send(new \Tishmalo\EscalationMatrix\Mail\CriticalErrorNotification($errorData, $contact, $priority));
                    
                        Log::info('Error notification email sent', [
                            'recipient' => $contact['email'],
                            'priority' => $priority,
                        ]);
                    } else {
                         Log::warning('CriticalErrorNotification Mailable not found.');
                    }
                   
                } catch (\Exception $e) {
                    Log::error('Failed to send error notification email', [
                        'recipient' => $contact['email'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Send SMS if channel is enabled, phone matches, and SMS is explicitly enabled in config
            // Making SMS optional
            $smsEnabled = config('escalation.sms_enabled', true); // Check general switch
            if ($smsEnabled && in_array('sms', $channels) && !empty($contact['phone'])) {
                try {
                    $template = $priority === 'critical' ? 'error_critical' : 'error_high';
                    $this->smsService->sendTemplate($contact['phone'], $template, [
                        'exception_type' => $errorData['exception']['type_short'],
                        'file' => basename($errorData['exception']['file']),
                        'line' => $errorData['exception']['line'],
                        'ticket_number' => $errorData['ticket_number'] ?? 'N/A',
                        'isp_name' => config('app.name', 'ISP'),
                    ]);

                    Log::info('Error notification SMS sent', [
                        'recipient' => $contact['phone'],
                        'priority' => $priority,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send error notification SMS', [
                        'recipient' => $contact['phone'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Create support ticket for the error via Driver
     */
    protected function createTicket(array $errorData): ?string
    {
        try {
            $subject = "[{$errorData['priority_label']}] {$errorData['exception']['type_short']}: {$errorData['exception']['message_short']}";
            $description = $this->formatTicketDescription($errorData);
            
            // Delegate to driver
            return $this->ticketDriver->createTicket($subject, $description, $errorData['priority'], $errorData);

        } catch (\Exception $e) {
            Log::error('Failed to create error ticket via driver', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Format error data for notifications and tickets
     */
    protected function formatErrorData(Throwable $exception, string $priority): array
    {
        $exceptionType = get_class($exception);
        $exceptionShortType = class_basename($exceptionType);

        return [
            'priority' => $priority,
            'priority_label' => strtoupper($priority),
            'exception' => [
                'type' => $exceptionType,
                'type_short' => $exceptionShortType,
                'message' => $exception->getMessage(),
                'message_short' => \Illuminate\Support\Str::limit($exception->getMessage(), 100),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ],
            'request' => [
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'input' => request()->except(['password', 'password_confirmation', '_token']),
            ],
            'user' => [
                'id' => auth()->id(),
                'email' => auth()->user()?->email,
                'name' => auth()->user()?->name ?? 'Guest',
                'authenticated' => auth()->check(),
            ],
            'environment' => [
                'app_env' => app()->environment(),
                'app_debug' => config('app.debug'),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'timestamp' => now()->toISOString(),
                'timezone' => config('app.timezone'),
            ],
            'server' => [
                'memory_usage' => $this->formatBytes(memory_get_usage(true)),
                'memory_peak' => $this->formatBytes(memory_get_peak_usage(true)),
            ],
        ];
    }

    /**
     * Format ticket description with error details
     */
    protected function formatTicketDescription(array $errorData): string
    {
        $description = "## Automatic Error Report\n\n";
        $description .= "**Priority:** {$errorData['priority_label']}\n";
        $description .= "**Exception:** {$errorData['exception']['type']}\n";
        $description .= "**Message:** {$errorData['exception']['message']}\n\n";

        $description .= "### Location\n";
        $description .= "**File:** {$errorData['exception']['file']}\n";
        $description .= "**Line:** {$errorData['exception']['line']}\n\n";

        $description .= "### Request Information\n";
        $description .= "**URL:** {$errorData['request']['method']} {$errorData['request']['url']}\n";
        $description .= "**IP:** {$errorData['request']['ip']}\n";
        $description .= "**User Agent:** {$errorData['request']['user_agent']}\n\n";

        $description .= "### User Context\n";
        if ($errorData['user']['authenticated']) {
            $description .= "**User ID:** {$errorData['user']['id']}\n";
            $description .= "**Email:** {$errorData['user']['email']}\n";
            $description .= "**Name:** {$errorData['user']['name']}\n\n";
        } else {
            $description .= "**Status:** Guest (not authenticated)\n\n";
        }

        $description .= "### Environment\n";
        $description .= "**Environment:** {$errorData['environment']['app_env']}\n";
        $description .= "**PHP Version:** {$errorData['environment']['php_version']}\n";
        $description .= "**Laravel Version:** {$errorData['environment']['laravel_version']}\n";
        $description .= "**Timestamp:** {$errorData['environment']['timestamp']}\n\n";

        $description .= "### Server Resources\n";
        $description .= "**Memory Usage:** {$errorData['server']['memory_usage']}\n";
        $description .= "**Memory Peak:** {$errorData['server']['memory_peak']}\n\n";

        $description .= "### Stack Trace\n";
        $description .= "```\n{$errorData['exception']['trace']}\n```\n";

        return $description;
    }

    /**
     * Check rate limiting to prevent spam
     */
    protected function checkRateLimit(Throwable $exception, string $priority): bool
    {
        $hash = $this->getExceptionHash($exception);
        $cacheKey = "error_notify:{$hash}";

        // Get rate limit TTL for this priority (in minutes)
        $ttl = config("escalation.rate_limit.{$priority}", 15);

        // Check if notification was recently sent
        if (Cache::has($cacheKey)) {
            return false;
        }

        return true;
    }

    /**
     * Record notification in cache to enforce rate limiting
     */
    protected function recordNotification(Throwable $exception, string $priority): void
    {
        $hash = $this->getExceptionHash($exception);
        $cacheKey = "error_notify:{$hash}";

        // Get rate limit TTL for this priority (in minutes)
        $ttl = config("escalation.rate_limit.{$priority}", 15);

        // Store in cache
        Cache::put($cacheKey, now()->toISOString(), now()->addMinutes($ttl));
    }

    /**
     * Generate unique hash for exception (for rate limiting)
     */
    protected function getExceptionHash(Throwable $exception): string
    {
        $data = get_class($exception) . $exception->getFile() . $exception->getLine();
        return md5($data);
    }

    /**
     * Format bytes to human-readable string
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
