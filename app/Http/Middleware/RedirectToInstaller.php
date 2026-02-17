<?php

namespace App\Http\Middleware;

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

        $envMissing = !file_exists(base_path('.env'));
        $installedLock = storage_path('framework/installed.lock');
        $isInstalled = file_exists($installedLock);

        if ($request->routeIs('install.*') || $request->is('install*')) {
            if ($isInstalled) {
                return redirect()->route('login');
            }

            return $next($request);
        }

        if (!$envMissing) {
            return $next($request);
        }

        return redirect()->route('install.index');
    }
}
