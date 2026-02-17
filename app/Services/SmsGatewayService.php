<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsGatewayService
{
    public function send(string $phone, string $message): bool
    {
        $provider = get_setting('sms_provider', 'none');
        if ($provider !== 'custom') {
            return false;
        }

        $url = (string) get_setting('sms_url', '');
        $apiKey = (string) get_setting('sms_api_key', '');
        if ($url === '' || $apiKey === '') {
            return false;
        }

        $sender = (string) get_setting('sms_sender_id', 'Hostel');
        $method = strtoupper((string) get_setting('sms_http_method', 'POST'));
        $payloadTemplate = json_decode((string) get_setting('sms_payload_template_json', ''), true);
        $headers = json_decode((string) get_setting('sms_custom_headers_json', ''), true);
        $headers = is_array($headers) ? array_filter($headers, fn ($v) => $v !== null && $v !== '') : [];

        $payload = [];
        if (!is_array($payloadTemplate) || empty($payloadTemplate)) {
            $payload = [
                'api_key' => $apiKey,
                'sender_id' => $sender,
                'to' => $phone,
                'message' => $message,
            ];
        } else {
            $replacements = [
                'phone' => $phone,
                'to' => $phone,
                'message' => $message,
                'sender_id' => $sender,
                'from' => $sender,
                'api_key' => $apiKey,
            ];

            foreach ($payloadTemplate as $key => $value) {
                $resolved = (string) $value;
                foreach ($replacements as $placeholder => $replacement) {
                    $resolved = str_replace('{' . $placeholder . '}', (string) $replacement, $resolved);
                }
                $payload[$key] = $resolved;
            }
        }

        $request = Http::withHeaders($headers);
        $response = $method === 'GET'
            ? $request->get($url, $payload)
            : $request->post($url, $payload);

        return $response->successful();
    }
}
