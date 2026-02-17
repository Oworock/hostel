<?php

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

test('password reset link can be requested with phone and sent by sms', function () {
    Http::fake([
        'https://sms.test.local/*' => Http::response(['ok' => true], 200),
    ]);

    SystemSetting::setSetting('sms_provider', 'custom');
    SystemSetting::setSetting('sms_url', 'https://sms.test.local/send');
    SystemSetting::setSetting('sms_api_key', 'test_api_key');
    SystemSetting::setSetting('sms_http_method', 'POST');
    SystemSetting::setSetting('sms_sender_id', 'HostelSys');
    SystemSetting::setSetting('sms_payload_template_json', json_encode([
        'to' => '{to}',
        'message' => '{message}',
        'api_key' => '{api_key}',
    ]));
    SystemSetting::setSetting('sms_custom_headers_json', json_encode([]));

    $user = User::create([
        'name' => 'Phone Reset User',
        'first_name' => 'Phone',
        'last_name' => 'User',
        'email' => 'phone-reset@example.com',
        'phone' => '+2348091112233',
        'password' => Hash::make('password123'),
        'role' => 'student',
        'is_active' => true,
    ]);

    $response = $this->post(route('password.email'), [
        'identifier' => '+2348091112233',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Password reset link sent to your phone by SMS.');

    expect(DB::table('password_reset_tokens')->where('email', $user->email)->exists())->toBeTrue();

    Http::assertSent(function ($request) {
        $data = $request->data();
        $message = (string) ($data['message'] ?? '');

        return $request->url() === 'https://sms.test.local/send'
            && ($data['to'] ?? null) === '+2348091112233'
            && str_contains($message, '/reset-password/');
    });
});

