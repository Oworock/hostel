<?php

namespace App\Http\Middleware;

use App\Support\InstallState;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToInstaller
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->runningInConsole()) {
            return $next($request);
        }

        $needsInstallation = InstallState::needsInstallation();

        if ($request->routeIs('install.*') || $request->is('install*')) {
            if (!$needsInstallation) {
                return redirect()->route('login');
            }

            return $next($request);
        }

        if ($needsInstallation) {
            return redirect()->route('install.index');
        }

        return $next($request);
    }
}
