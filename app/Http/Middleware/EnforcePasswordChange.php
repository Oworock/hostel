<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        if (!($user->is_admin_uploaded && $user->must_change_password)) {
            return $next($request);
        }

        $allowedRoutes = [
            'logout',
            'student.profile.edit',
            'student.profile.update',
            'manager.profile.edit',
            'manager.profile.update',
        ];

        if ($request->route() && in_array($request->route()->getName(), $allowedRoutes, true)) {
            return $next($request);
        }

        $target = $user->isManager()
            ? route('manager.profile.edit')
            : route('student.profile.edit');

        return redirect($target)->with('error', 'Please change your password to continue.');
    }
}
