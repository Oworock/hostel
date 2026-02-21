<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

class SystemUpdateService
{
    /**
     * @return array{version: string|null, zip_url: string|null, notes: string|null}
     */
    public function fetchRemoteManifest(string $manifestUrl): array
    {
        $manifestUrl = trim($manifestUrl);
        if ($manifestUrl === '' || !filter_var($manifestUrl, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('Update manifest URL is invalid.');
        }

        $response = Http::timeout(20)->get($manifestUrl);
        if (!$response->ok()) {
            throw new RuntimeException('Unable to fetch update manifest.');
        }

        $payload = $response->json();
        if (!is_array($payload)) {
            throw new RuntimeException('Update manifest is not valid JSON.');
        }

        $zipUrl = trim((string) ($payload['zip_url'] ?? ''));
        if ($zipUrl !== '' && !filter_var($zipUrl, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('Manifest zip_url is invalid.');
        }

        return [
            'version' => trim((string) ($payload['version'] ?? '')) ?: null,
            'zip_url' => $zipUrl !== '' ? $zipUrl : null,
            'notes' => trim((string) ($payload['notes'] ?? '')) ?: null,
        ];
    }

    /**
     * Downloads a remote zip update into storage/app/updates.
     */
    public function downloadRemotePackage(string $zipUrl): string
    {
        $zipUrl = trim($zipUrl);
        if ($zipUrl === '' || !filter_var($zipUrl, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('Update zip URL is invalid.');
        }

        $response = Http::timeout(120)->get($zipUrl);
        if (!$response->ok()) {
            throw new RuntimeException('Unable to download update zip file.');
        }

        $body = $response->body();
        if ($body === '') {
            throw new RuntimeException('Downloaded update file is empty.');
        }

        $path = 'updates/update_' . now()->format('Ymd_His') . '.zip';
        Storage::disk('local')->put($path, $body);

        return $path;
    }

    /**
     * @return array{
     *   package: string,
     *   files_total: int,
     *   create: int,
     *   update: int,
     *   unchanged: int,
     *   affected: array<int, array{path: string, status: string}>
     * }
     */
    public function previewFromStoredPath(string $localPath): array
    {
        $zipPath = Storage::disk('local')->path($localPath);
        if (!is_file($zipPath)) {
            throw new RuntimeException('Update zip file was not found.');
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('Unable to open update zip file.');
        }

        try {
            $affected = [];
            $create = 0;
            $update = 0;
            $unchanged = 0;

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = (string) $zip->getNameIndex($i);
                if ($entryName === '' || str_ends_with($entryName, '/')) {
                    continue;
                }

                $safePath = $this->sanitizeEntryPath($entryName);
                if ($safePath === null) {
                    continue;
                }

                $stream = $zip->getStream($entryName);
                if ($stream === false) {
                    throw new RuntimeException('Unable to read zip entry: ' . $entryName);
                }
                $contents = stream_get_contents($stream);
                fclose($stream);
                if ($contents === false) {
                    throw new RuntimeException('Unable to read zip entry: ' . $entryName);
                }

                $target = base_path($safePath);
                if (!file_exists($target)) {
                    $status = 'create';
                    $create++;
                } elseif (sha1_file($target) === sha1($contents)) {
                    $status = 'unchanged';
                    $unchanged++;
                } else {
                    $status = 'update';
                    $update++;
                }

                $affected[] = [
                    'path' => $safePath,
                    'status' => $status,
                ];
            }

            return [
                'package' => $localPath,
                'files_total' => count($affected),
                'create' => $create,
                'update' => $update,
                'unchanged' => $unchanged,
                'affected' => $affected,
            ];
        } finally {
            $zip->close();
        }
    }

    /**
     * @param array<int, string>|null $selectedPaths
     * @return array{applied: int}
     */
    public function applyFromStoredPath(string $localPath, ?array $selectedPaths = null): array
    {
        $zipPath = Storage::disk('local')->path($localPath);
        if (!is_file($zipPath)) {
            throw new RuntimeException('Update zip file was not found.');
        }

        $allowed = $selectedPaths !== null ? array_fill_keys($selectedPaths, true) : null;

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('Unable to open update zip file.');
        }

        $applied = 0;
        try {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = (string) $zip->getNameIndex($i);
                if ($entryName === '' || str_ends_with($entryName, '/')) {
                    continue;
                }

                $safePath = $this->sanitizeEntryPath($entryName);
                if ($safePath === null) {
                    continue;
                }

                if ($allowed !== null && !isset($allowed[$safePath])) {
                    continue;
                }

                $stream = $zip->getStream($entryName);
                if ($stream === false) {
                    throw new RuntimeException('Unable to read zip entry: ' . $entryName);
                }
                $contents = stream_get_contents($stream);
                fclose($stream);
                if ($contents === false) {
                    throw new RuntimeException('Unable to read zip entry: ' . $entryName);
                }

                $target = base_path($safePath);
                File::ensureDirectoryExists(dirname($target));
                file_put_contents($target, $contents);
                $applied++;
            }
        } finally {
            $zip->close();
        }

        return ['applied' => $applied];
    }

    private function sanitizeEntryPath(string $entryPath): ?string
    {
        $entryPath = trim(str_replace('\\', '/', $entryPath), '/');
        if ($entryPath === '') {
            return null;
        }

        $parts = array_values(array_filter(explode('/', $entryPath), static fn (string $part): bool => $part !== '' && $part !== '.'));
        if (empty($parts)) {
            return null;
        }
        foreach ($parts as $part) {
            if ($part === '..') {
                throw new RuntimeException('Unsafe path detected in update zip.');
            }
        }

        $clean = implode('/', $parts);
        if (!$this->isAllowedPath($clean)) {
            return null;
        }

        return $clean;
    }

    private function isAllowedPath(string $path): bool
    {
        $blocked = ['.env', 'storage/', 'bootstrap/cache/', 'vendor/', 'node_modules/'];
        foreach ($blocked as $prefix) {
            if ($path === rtrim($prefix, '/') || str_starts_with($path, $prefix)) {
                return false;
            }
        }

        $allowedRoots = [
            'app/',
            'bootstrap/',
            'config/',
            'database/',
            'public/',
            'resources/',
            'routes/',
            'system/',
            'docs/',
        ];
        foreach ($allowedRoots as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        $allowedFiles = [
            'composer.json',
            'composer.lock',
            'package.json',
            'vite.config.js',
            'artisan',
        ];

        return in_array($path, $allowedFiles, true);
    }
}

