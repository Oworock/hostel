<?php

namespace App\Services;

use App\Models\Addon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AddonDiscoveryService
{
    public function __construct(
        private readonly AddonVisibilityService $visibilityService
    ) {
    }

    public function discover(): void
    {
        $this->discoverBuiltIn();
        $this->discoverFromLocalStorage();
    }

    private function discoverBuiltIn(): void
    {
        $basePath = base_path('addons');
        if (!is_dir($basePath)) {
            return;
        }

        $manifests = glob($basePath . '/*/addon.json') ?: [];
        foreach ($manifests as $manifestPath) {
            $manifest = json_decode((string) file_get_contents($manifestPath), true);
            if (!is_array($manifest)) {
                continue;
            }

            $this->upsertFromManifest($manifest, dirname($manifestPath));
        }
    }

    private function discoverFromLocalStorage(): void
    {
        foreach (Storage::disk('local')->allFiles('addons') as $file) {
            if (!str_ends_with($file, '/addon.json')) {
                continue;
            }

            $raw = Storage::disk('local')->get($file);
            $manifest = json_decode($raw, true);
            if (!is_array($manifest)) {
                continue;
            }

            $this->upsertFromManifest($manifest, dirname($file));
        }
    }

    /**
     * @param array<string, mixed> $manifest
     */
    private function upsertFromManifest(array $manifest, string $location): void
    {
        $name = trim((string) ($manifest['name'] ?? ''));
        $slug = Str::slug((string) ($manifest['slug'] ?? $name));

        if ($name === '' || $slug === '') {
            return;
        }

        if ($this->visibilityService->isIgnored($slug)) {
            return;
        }

        Addon::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'version' => (string) ($manifest['version'] ?? '1.0.0'),
                'description' => (string) ($manifest['description'] ?? ''),
                'manifest' => $manifest,
                'extracted_path' => $location,
            ]
        );
    }
}
