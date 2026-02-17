@extends('layouts.app')

@section('title', 'API Documentation')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-3xl font-bold text-gray-900">API Documentation</h1>
            <p class="text-gray-600 mt-2">Use this API to manage hostels, rooms, students, bookings, payments and complaints.</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 space-y-4">
            <h2 class="text-xl font-semibold text-gray-900">Authentication</h2>
            <p class="text-gray-700">Enable API in <strong>System Settings -> Integrations</strong>, then use one of the following headers:</p>
            <pre class="bg-gray-900 text-gray-100 rounded-lg p-4 text-sm overflow-x-auto"><code>Authorization: Bearer YOUR_API_ACCESS_KEY</code></pre>
            <p class="text-gray-500 text-sm">or</p>
            <pre class="bg-gray-900 text-gray-100 rounded-lg p-4 text-sm overflow-x-auto"><code>X-API-Key: YOUR_API_ACCESS_KEY</code></pre>
            <p class="text-sm text-gray-600">Base URL: <code class="bg-gray-100 px-2 py-1 rounded">{{ url('/') }}</code></p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between gap-3 mb-4">
                <h2 class="text-xl font-semibold text-gray-900">OpenAPI Spec</h2>
                <a href="{{ $openApiUrl }}" target="_blank" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium">Open JSON</a>
            </div>
            <p class="text-gray-600 text-sm">Use the JSON spec for external tooling (Postman import, SDK generation, automation tools).</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 space-y-4">
            <h2 class="text-xl font-semibold text-gray-900">Webhook Documentation</h2>
            <p class="text-gray-700">Configure outgoing webhooks in <strong>System Settings -> Integrations -> Outgoing Webhooks</strong>. The system sends events to your URL (Zapier, Make, n8n, custom backend).</p>
            <div>
                <h3 class="text-sm font-semibold text-gray-900 mb-2">Headers Sent</h3>
                <pre class="bg-gray-900 text-gray-100 rounded-lg p-4 text-sm overflow-x-auto"><code>Content-Type: application/json
X-Hostel-Event: EVENT_NAME
X-Hostel-Signature: HMAC_SHA256_SIGNATURE</code></pre>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-900 mb-2">Payload Shape</h3>
                <pre class="bg-gray-900 text-gray-100 rounded-lg p-4 text-sm overflow-x-auto"><code>{
  "id": "uuid",
  "event": "booking.created",
  "timestamp": "2026-02-17T18:45:00+00:00",
  "payload": {
    "...": "event-specific data",
    "users": {
      "actor": { "...": "full actor profile" },
      "student": { "...": "full student profile" },
      "manager": { "...": "full manager profile" },
      "admin": { "...": "full admin profile" },
      "user": { "...": "generic user profile" }
    }
  }
}</code></pre>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-900 mb-2">User Data Included In <code>payload.users</code></h3>
                <p class="text-sm text-gray-700 mb-3">When your event payload contains <code>student_id</code>, <code>manager_id</code>, <code>admin_id</code>, or <code>user_id</code>, the webhook automatically resolves and sends full profile details. <code>actor</code> is the currently authenticated user that triggered the action.</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                    @foreach($webhookUserFields as $field)
                        <code class="bg-gray-100 text-gray-800 text-xs px-3 py-2 rounded">{{ $field }}</code>
                    @endforeach
                </div>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-900 mb-2">Full User Example</h3>
                <pre class="bg-gray-900 text-gray-100 rounded-lg p-4 text-sm overflow-x-auto"><code>{
  "users": {
    "student": {
      "id": 12,
      "name": "Student User",
      "first_name": "Student",
      "last_name": "User",
      "email": "student@example.com",
      "phone": "+2348090000000",
      "role": "student",
      "hostel_id": 2,
      "hostel_name": "MVP Phase 2, Utako - Abuja",
      "id_number": "MAT2026-001",
      "address": "Abuja",
      "guardian_name": "Jane Doe",
      "guardian_phone": "+2348012345678",
      "is_active": true,
      "is_admin_uploaded": false,
      "must_change_password": false,
      "profile_image_url": "https://your-domain/storage/profile-images/...",
      "extra_data": { "department": "Computer Science" },
      "created_at": "2026-02-10T09:00:00+00:00",
      "updated_at": "2026-02-17T10:30:00+00:00"
    }
  }
}</code></pre>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-900 mb-2">Signature Verification (PHP)</h3>
                <pre class="bg-gray-900 text-gray-100 rounded-lg p-4 text-sm overflow-x-auto"><code>$rawBody = file_get_contents('php://input');
$incoming = $_SERVER['HTTP_X_HOSTEL_SIGNATURE'] ?? '';
$expected = hash_hmac('sha256', $rawBody, $yourWebhookSecret);
$isValid = hash_equals($expected, $incoming);</code></pre>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-900 mb-2">Available Webhook Events</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                    @foreach($webhookEvents as $event)
                        <code class="bg-gray-100 text-gray-800 text-sm px-3 py-2 rounded">{{ $event }}</code>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Endpoints</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Method</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Path</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Description</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($endpoints as $endpoint)
                            <tr>
                                <td class="px-6 py-3">
                                    <span class="inline-flex px-2.5 py-1 rounded text-xs font-bold
                                        {{ $endpoint['method'] === 'GET' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $endpoint['method'] === 'POST' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $endpoint['method'] === 'PATCH' ? 'bg-amber-100 text-amber-800' : '' }}
                                        {{ $endpoint['method'] === 'DELETE' ? 'bg-red-100 text-red-800' : '' }}
                                    ">
                                        {{ $endpoint['method'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 font-mono text-sm text-gray-900">{{ $endpoint['path'] }}</td>
                                <td class="px-6 py-3 text-sm text-gray-700">{{ $endpoint['description'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 space-y-4">
            <h2 class="text-xl font-semibold text-gray-900">Quick Example</h2>
            <pre class="bg-gray-900 text-gray-100 rounded-lg p-4 text-sm overflow-x-auto"><code>curl -X GET "{{ url('/api/v1/hostels') }}" \
  -H "Authorization: Bearer YOUR_API_ACCESS_KEY" \
  -H "Accept: application/json"</code></pre>
        </div>
    </div>
</div>
@endsection
