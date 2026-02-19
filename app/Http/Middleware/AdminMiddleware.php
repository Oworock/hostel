<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user() ?? auth('web')->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $role = strtolower(trim((string) $user->role));
        if (!in_array($role, ['admin', 'super_admin'], true)) {
            if ($user->isManager()) {
                return redirect()->route('manager.bookings.index');
            }

            if ($user->isStudent()) {
                return redirect()->route('student.bookings.index');
            }

            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
