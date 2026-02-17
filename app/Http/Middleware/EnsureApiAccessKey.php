<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiAccessKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $enabled = filter_var(get_setting('api_enabled', false), FILTER_VALIDATE_BOOL);
        if (!$enabled) {
            return response()->json(['message' => 'API access is disabled.'], 403);
        }

        $provided = (string) ($request->bearerToken() ?: $request->header('X-API-Key', ''));
        $configured = (string) get_setting('api_access_key', '');

        if ($configured === '' || $provided === '' || !hash_equals($configured, $provided)) {
            return response()->json(['message' => 'Unauthorized API key.'], 401);
        }

        return $next($request);
    }
}
