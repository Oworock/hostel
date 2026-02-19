<?php

namespace App\Services;

use App\Models\Addon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class AddonPackageService
{
    public function registerFromStoredPackagePath(string $packagePath, ?int $uploadedBy = null): Addon
    {
        if (!Storage::disk('local')->exists($packagePath)) {
            throw new RuntimeException('Uploaded addon package file was not found.');
        }

        if (strtolower(pathinfo($packagePath, PATHINFO_EXTENSION)) !== 'zip') {
            throw new RuntimeException('Addon package must be a ZIP archive.');
        }

        $fullPackagePath = Storage::disk('local')->path($packagePath);
        $zip = new ZipArchive();

        if ($zip->open($fullPackagePath) !== true) {
            throw new RuntimeException('Unable to open addon package.');
        }

        $manifestRaw = $zip->getFromName('addon.json');
        if ($manifestRaw === false) {
            $zip->close();
            throw new RuntimeException('Addon package is missing addon.json at archive root.');
        }

        $manifest = json_decode($manifestRaw, true);
        if (!is_array($manifest)) {
            $zip->close();
            throw new RuntimeException('addon.json contains invalid JSON.');
        }

        $name = trim((string) ($manifest['name'] ?? ''));
        $slugCandidate = trim((string) ($manifest['slug'] ?? $name));
        $version = trim((string) ($manifest['version'] ?? '1.0.0'));
        $description = trim((string) ($manifest['description'] ?? ''));

        if ($name === '' || $slugCandidate === '') {
            $zip->close();
            throw new RuntimeException('addon.json must include name (and optionally slug).');
        }

        $slug = Str::slug($slugCandidate);
        if ($slug === '') {
            $zip->close();
            throw new RuntimeException('Addon slug resolved to an empty value.');
        }

        $extractRoot = 'addons/extracted/' . $slug . '/' . now()->format('YmdHis');
        $this->extractSafely($zip, $extractRoot);
        $zip->close();

        return Addon::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'version' => $version,
                'description' => $description !== '' ? $description : null,
                'package_path' => $packagePath,
                'extracted_path' => $extractRoot,
                'manifest' => $manifest,
                'uploaded_by' => $uploadedBy,
                'installed_at' => now(),
            ]
        );
    }

    private function extractSafely(ZipArchive $zip, string $destinationRoot): void
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = (string) $zip->getNameIndex($i);
            $cleanEntry = $this->sanitizeArchivePath($entryName);

            if ($cleanEntry === null || $cleanEntry === '') {
                continue;
            }

            $fullTarget = $destinationRoot . '/' . $cleanEntry;

            if (str_ends_with($entryName, '/')) {
                Storage::disk('local')->makeDirectory($fullTarget);
                continue;
            }

            $stream = $zip->getStream($entryName);
            if ($stream === false) {
                throw new RuntimeException('Failed to read archive entry: ' . $entryName);
            }

            $contents = stream_get_contents($stream);
            fclose($stream);

            if ($contents === false) {
                throw new RuntimeException('Failed to extract archive entry: ' . $entryName);
            }

            $dir = dirname($fullTarget);
            if ($dir !== '.' && $dir !== '') {
                Storage::disk('local')->makeDirectory($dir);
            }

            Storage::disk('local')->put($fullTarget, $contents);
        }
    }

    private function sanitizeArchivePath(string $entry): ?string
    {
        $entry = str_replace('\\', '/', trim($entry));
        if ($entry === '' || str_starts_with($entry, '/')) {
            return null;
        }

        $parts = array_values(array_filter(explode('/', $entry), fn (string $part): bool => $part !== '' && $part !== '.'));
        if (empty($parts)) {
            return null;
        }

        foreach ($parts as $part) {
            if ($part === '..') {
                throw new RuntimeException('Unsafe archive path detected in addon package.');
            }
        }

        return implode('/', $parts);
    }
}
