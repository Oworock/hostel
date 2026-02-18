<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        $databaseReady = !$envMissing && $this->isDatabaseReady();
        $needsInstallation = $envMissing || !$isInstalled || !$databaseReady;

        if ($request->routeIs('install.*') || $request->is('install*')) {
            if (!$needsInstallation && $isInstalled) {
                return redirect()->route('login');
            }

            return $next($request);
        }

        if ($needsInstallation) {
            return redirect()->route('install.index');
        }

        return $next($request);
    }

    private function isDatabaseReady(): bool
    {
        try {
            DB::connection()->getPdo();

            return Schema::hasTable('migrations')
                && Schema::hasTable('users')
                && Schema::hasTable('system_settings');
        } catch (\Throwable $e) {
            return false;
        }
    }
}
