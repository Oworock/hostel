<?php

namespace App\Support;

class SystemTranslationStore
{
    /**
     * @return array<int, array{locale: string, key: string, value: string}>
     */
    public static function read(): array
    {
        $path = self::path();
        if (!is_file($path)) {
            return [];
        }

        $raw = @file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        $rows = $decoded['translations'] ?? $decoded;
        if (!is_array($rows)) {
            return [];
        }

        $normalized = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $locale = trim((string) ($row['locale'] ?? ''));
            $key = trim((string) ($row['key'] ?? ''));
            $value = trim((string) ($row['value'] ?? ''));
            if ($locale === '' || $key === '' || $value === '') {
                continue;
            }
            $normalized[] = [
                'locale' => $locale,
                'key' => $key,
                'value' => $value,
            ];
        }

        return $normalized;
    }

    /**
     * @param array<int, array{locale: string, key: string, value: string}> $rows
     */
    public static function write(array $rows): void
    {
        $path = self::path();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $payload = [
            'updated_at' => now()->toIso8601String(),
            'translations' => array_values($rows),
        ];

        file_put_contents(
            $path,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    public static function path(): string
    {
        return base_path('system/translations/translations.json');
    }
}

