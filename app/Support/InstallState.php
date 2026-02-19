<?php

namespace App\Support;

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
        return !self::hasDatabaseConfiguration() || !self::appInstalledFlag();
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
