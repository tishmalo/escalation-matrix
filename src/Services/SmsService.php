<?php

namespace Tishmalo\EscalationMatrix\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $username;
    protected string $apiKey;
    protected string $senderId;
    protected string $baseUrl = 'https://api.africastalking.com/version1/messaging';

    public function __construct()
    {
        $this->username = config('services.africastalking.username', '');
        $this->apiKey = config('services.africastalking.api_key', '');
        $this->senderId = config('services.africastalking.sender_id', '');
    }

    /**
     * Send SMS notification
     */
    public function send(string $to, string $message): bool
    {
        // Check if SMS is enabled in package config, default to true if not specified to maintain backward comaptibility or rely on caller checks
        // But since we want it optional, the caller (EscalationService) will likely handle the higher level check.
        // Here we just check if creds are present.
        if (empty($this->username) || empty($this->apiKey)) {
            Log::warning('SMS Service: Missing credentials.');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'apiKey' => $this->apiKey,
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
            ])->asForm()->post($this->baseUrl, [
                'username' => $this->username,
                'to' => $this->formatPhoneNumber($to),
                'message' => $message,
                'from' => $this->senderId,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['SMSMessageData']['Recipients'][0]['status']) &&
                    $data['SMSMessageData']['Recipients'][0]['status'] === 'Success') {
                    Log::info("SMS sent successfully to {$to}");
                    return true;
                }
            }

            Log::error("Failed to send SMS to {$to}", [
                'response' => $response->body(),
            ]);
            return false;
        } catch (Exception $e) {
            Log::error("SMS sending failed: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Send template SMS (Adapted for Error Notification usage)
     */
    public function sendTemplate(string $to, string $template, array $data): bool
    {
        $message = $this->renderMessage($template, $data);
        return $this->send($to, $message);
    }

    protected function renderMessage(string $template, array $data): string
    {
        if ($template === 'error_critical') {
            return "CRITICAL ERROR: {$data['exception_type']} in {$data['file']}:{$data['line']}. Ticket: {$data['ticket_number']}. {$data['isp_name']}";
        }
        
        if ($template === 'error_high') {
             return "HIGH ERROR: {$data['exception_type']}. Ticket: {$data['ticket_number']}. {$data['isp_name']}";
        }

        return "Error notification from {$data['isp_name']}";
    }

    /**
     * Format phone number to international format
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove any spaces or special characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Convert to international format if needed
        if (str_starts_with($phone, '0')) {
            $phone = '+254' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        return $phone;
    }
}
