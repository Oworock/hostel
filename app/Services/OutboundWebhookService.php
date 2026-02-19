<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class OutboundWebhookService
{
    public function dispatch(string $event, array $payload = [], bool $ignoreEventFilter = false): bool
    {
        $enabled = filter_var(get_setting('webhook_enabled', false), FILTER_VALIDATE_BOOL);
        $url = trim((string) get_setting('webhook_url', ''));

        if (!$enabled || $url === '') {
            return false;
        }

        $allowedEvents = json_decode((string) get_setting('webhook_events_json', '[]'), true);
        if (!$ignoreEventFilter && is_array($allowedEvents) && !empty($allowedEvents) && !in_array($event, $allowedEvents, true)) {
            return false;
        }

        $body = [
            'id' => (string) Str::uuid(),
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
            'payload' => $this->enrichPayloadWithUsers($payload),
        ];

        try {
            $response = Http::acceptJson()
                ->timeout(15)
                ->retry(1, 250)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Hostel-Event' => $event,
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

    private function enrichPayloadWithUsers(array $payload): array
    {
        $relatedUserIds = $this->extractRelatedUserIds($payload);
        $actor = Auth::user();

        if ($actor && !isset($relatedUserIds['actor'])) {
            $relatedUserIds['actor'] = (int) $actor->id;
        }

        if (empty($relatedUserIds)) {
            return $payload;
        }

        $users = User::query()
            ->with(['hostel:id,name'])
            ->whereIn('id', array_values($relatedUserIds))
            ->get()
            ->keyBy('id');

        $payload['users'] = [];
        foreach ($relatedUserIds as $role => $userId) {
            $user = $users->get($userId);
            if (!$user) {
                continue;
            }

            $payload['users'][$role] = $this->serializeUser($user);
        }

        return $payload;
    }

    private function extractRelatedUserIds(array $payload): array
    {
        $pairs = [];
        $this->walkPayload($payload, $pairs);

        $clean = [];
        foreach ($pairs as $role => $id) {
            if (is_numeric($id) && (int) $id > 0) {
                $clean[$role] = (int) $id;
            }
        }

        return $clean;
    }

    private function walkPayload(array $payload, array &$pairs): void
    {
        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $this->walkPayload($value, $pairs);
                continue;
            }

            if (!is_scalar($value)) {
                continue;
            }

            if (!is_string($key)) {
                continue;
            }

            if (preg_match('/^(user|student|manager|admin|requested_by|created_by|reported_by|receiving_manager)_id$/', $key, $matches) === 1) {
                $role = $matches[1];
                if ($role === 'requested_by') {
                    $role = 'requester';
                } elseif ($role === 'created_by') {
                    $role = 'creator';
                } elseif ($role === 'reported_by') {
                    $role = 'reporter';
                } elseif ($role === 'receiving_manager') {
                    $role = 'receiving_manager';
                }
                $pairs[$role] = (int) $value;
            }
        }
    }

    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'hostel_id' => $user->hostel_id,
            'hostel_name' => $user->hostel?->name,
            'id_number' => $user->id_number,
            'address' => $user->address,
            'guardian_name' => $user->guardian_name,
            'guardian_phone' => $user->guardian_phone,
            'is_active' => (bool) $user->is_active,
            'is_admin_uploaded' => (bool) $user->is_admin_uploaded,
            'must_change_password' => (bool) $user->must_change_password,
            'profile_image_url' => $user->profile_image ? asset('storage/' . $user->profile_image) : null,
            'extra_data' => $user->extra_data ?? [],
            'created_at' => optional($user->created_at)?->toIso8601String(),
            'updated_at' => optional($user->updated_at)?->toIso8601String(),
        ];
    }
}
