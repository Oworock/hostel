<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleImpersonation
{
    public function handle(Request $request, Closure $next)
    {
        if (session('impersonated_user_id') && auth()->user()?->role === 'admin') {
            $user = \App\Models\User::find(session('impersonated_user_id'));
            if ($user) {
                auth()->setUser($user);
            }
        }

        return $next($request);
    }
}
