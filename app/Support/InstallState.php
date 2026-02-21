<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InstallState
{
    public static function appInstalledFlag(): bool
    {
        $env = self::readEnvFile();

        return filter_var($env['APP_INSTALLED'] ?? false, FILTER_VALIDATE_BOOL);
    }

    public static function hasDatabaseConfiguration(): bool
    {
        $env = self::readEnvFile();
        $connection = trim((string) ($env['DB_CONNECTION'] ?? ''));
        $database = trim((string) ($env['DB_DATABASE'] ?? ''));

        if ($connection === '' || $database === '') {
            return false;
        }

        if ($connection === 'sqlite') {
            return true;
        }

        $host = trim((string) ($env['DB_HOST'] ?? ''));
        $username = trim((string) ($env['DB_USERNAME'] ?? ''));

        return $host !== '' && $username !== '';
    }

    public static function needsInstallation(): bool
    {
        if (!file_exists(base_path('.env'))) {
            return true;
        }

        return !self::hasDatabaseConfiguration()
            || !self::appInstalledFlag();
    }

    public static function isRuntimeReady(): bool
    {
        if (!file_exists(base_path('vendor/autoload.php'))) {
            return false;
        }

        $env = self::readEnvFile();
        if (trim((string) ($env['APP_KEY'] ?? '')) === '') {
            return false;
        }

        if (!self::hasDatabaseConfiguration()) {
            return false;
        }

        try {
            DB::connection()->getPdo();

            if (!Schema::hasTable('users') || !Schema::hasTable('system_settings')) {
                return false;
            }
        } catch (\Throwable $e) {
            return false;
        }

        return true;
    }

    /**
     * @return array<string, string>
     */
    private static function readEnvFile(): array
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            return [];
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines)) {
            return [];
        }

        $values = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#') || !str_contains($trimmed, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $trimmed, 2);
            $key = trim($key);
            $value = trim($value);

            if ($key === '') {
                continue;
            }

            $values[$key] = trim($value, "\"'");
        }

        return $values;
    }
}
