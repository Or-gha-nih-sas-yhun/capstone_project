<?php

namespace App\Services;

use App\Models\Resident;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send an SMS through the configured PhilSMS gateway.
     */
    public static function send(string $number, string $message): bool
    {
        $enabled = config('services.philsms.enabled', true);
        $apiToken = trim((string) config('services.philsms.api_token'));
        $senderId = trim((string) config('services.philsms.sender_id', 'PhilSMS'));
        $apiUrl = config('services.philsms.api_url', 'https://dashboard.philsms.com/api/v3/sms/send');

        if (!filter_var($enabled, FILTER_VALIDATE_BOOLEAN)) {
            Log::info('SmsService: PHILSMS_ENABLED is false. SMS not sent.');
            return false;
        }

        if (empty($apiToken)) {
            Log::warning('SmsService: PHILSMS_API_TOKEN is not configured. SMS not sent.');
            return false;
        }

        // PhilSMS expects a Philippine number in 639xxxxxxxxx country-code format.
        $normalized = preg_replace('/[^0-9+]/', '', $number);
        $normalized = ltrim($normalized, '+');
        if (str_starts_with($normalized, '09')) {
            $normalized = '63' . substr($normalized, 1);
        } elseif (preg_match('/^9\d{9}$/', $normalized)) {
            $normalized = '63' . $normalized;
        }

        if (!preg_match('/^639\d{9}$/', $normalized)) {
            Log::warning('SmsService: Invalid Philippine mobile number. SMS not sent.');
            return false;
        }

        try {
            // Remove accidental query parameters from a copied gateway URL.
            $apiUrl = strtok($apiUrl, '?');

            $response = Http::timeout(15)
                ->acceptJson()
                ->withToken($apiToken)
                ->post($apiUrl, [
                    'recipient' => $normalized,
                    'sender_id' => $senderId,
                    'type' => 'plain',
                    'message' => $message,
                ]);

            $apiStatus = strtolower((string) $response->json('status'));
            if (!$response->successful() || $apiStatus === 'error') {
                $gatewayMessage = (string) ($response->json('message') ?: 'No error message returned');
                $gatewayMessage = str_replace(["\r", "\n"], ' ', $gatewayMessage);
                Log::error("SmsService: Gateway rejected SMS to {$normalized}. HTTP {$response->status()}: {$gatewayMessage}");
                return false;
            }

            Log::info("SmsService: SMS sent to {$normalized}.");
            return true;
        } catch (\Throwable $e) {
            Log::error("SmsService: Failed to send SMS to {$normalized}. Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a resident notification when a contact number is available.
     */
    public static function notifyResident(?Resident $resident, string $message, string $context): bool
    {
        if (!$resident || empty($resident->contact_number)) {
            Log::info("SmsService: No resident contact number for {$context}. SMS not sent.");
            return false;
        }

        $sent = self::send($resident->contact_number, $message);

        if (!$sent) {
            Log::warning("SmsService: Resident notification was not sent for {$context}.");
        }

        return $sent;
    }
}
