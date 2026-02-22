<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SystemUpdateMaintenanceController extends Controller
{
    public function ping(Request $request): JsonResponse
    {
        $token = trim((string) $request->query('token', ''));
        $expected = $this->resolvePingToken();

        abort_unless($expected !== '' && hash_equals($expected, $token), 403, 'Invalid ping token.');

        Artisan::call('optimize:clear');
        Artisan::call('view:clear');

        return response()->json([
            'ok' => true,
            'message' => 'Optimization and view cache cleared.',
        ]);
    }

    private function resolvePingToken(): string
    {
        $configured = trim((string) SystemSetting::getSetting('update_ping_token', ''));
        if ($configured !== '') {
            return $configured;
        }

        return substr(sha1((string) config('app.key') . '|update-ping-token'), 0, 32);
    }
}

