<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class PreventSpamRegistration
{
    public function handle(Request $request, Closure $next): Response
    {
        $honeypot = (string) $request->input('website', '');
        if ($honeypot !== '') {
            abort(422, 'Spam validation failed.');
        }

        $startedAt = (int) $request->input('form_started_at', 0);
        if ($startedAt > 0 && (time() - $startedAt) < 2) {
            abort(422, 'Form submitted too quickly.');
        }

        $rateKey = 'register:' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateKey, 15)) {
            abort(429, 'Too many registration attempts. Try again later.');
        }

        RateLimiter::hit($rateKey, 3600);

        return $next($request);
    }
}
