<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleImpersonation
{
    public function handle(Request $request, Closure $next)
    {
        // Legacy cleanup for older impersonation implementation.
        if (session()->has('impersonated_user_id')) {
            session()->forget('impersonated_user_id');
        }

        return $next($request);
    }
}
