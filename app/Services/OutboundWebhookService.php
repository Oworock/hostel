<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class OutboundWebhookService
{
    public function dispatch(string $event, array $payload = []): bool
    {
        $enabled = filter_var(get_setting('webhook_enabled', false), FILTER_VALIDATE_BOOL);
        $url = trim((string) get_setting('webhook_url', ''));
        $secret = (string) get_setting('webhook_secret', '');

        if (!$enabled || $url === '') {
            return false;
        }

        $allowedEvents = json_decode((string) get_setting('webhook_events_json', '[]'), true);
        if (is_array($allowedEvents) && !empty($allowedEvents) && !in_array($event, $allowedEvents, true)) {
            return false;
        }

        $body = [
            'id' => (string) Str::uuid(),
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
            'payload' => $payload,
        ];

        $bodyJson = json_encode($body, JSON_UNESCAPED_SLASHES);
        $signature = $secret !== '' ? hash_hmac('sha256', (string) $bodyJson, $secret) : '';

        try {
            $response = Http::acceptJson()
                ->timeout(15)
                ->retry(1, 250)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Hostel-Event' => $event,
                    'X-Hostel-Signature' => $signature,
                ])
                ->post($url, $body);

            if (!$response->successful()) {
                Log::warning('Outbound webhook failed.', [
                    'event' => $event,
                    'status' => $response->status(),
                    'url' => $url,
                    'response' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (Throwable $e) {
            Log::warning('Outbound webhook exception.', [
                'event' => $event,
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
