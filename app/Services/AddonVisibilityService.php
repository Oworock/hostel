<?php

namespace App\Services;

use App\Models\SystemSetting;

class AddonVisibilityService
{
    private const IGNORE_KEY = 'addon_ignored_slugs_json';

    /**
     * @return array<int, string>
     */
    public function ignoredSlugs(): array
    {
        $raw = (string) SystemSetting::getSetting(self::IGNORE_KEY, '[]');
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            fn ($slug) => trim((string) $slug),
            $decoded
        ))));
    }

    public function isIgnored(string $slug): bool
    {
        return in_array(trim($slug), $this->ignoredSlugs(), true);
    }

    public function ignore(string $slug): void
    {
        $slug = trim($slug);
        if ($slug === '') {
            return;
        }

        $items = $this->ignoredSlugs();
        if (!in_array($slug, $items, true)) {
            $items[] = $slug;
        }

        SystemSetting::setSetting(self::IGNORE_KEY, json_encode(array_values($items)), 'json');
    }

    public function unignore(string $slug): void
    {
        $slug = trim($slug);
        if ($slug === '') {
            return;
        }

        $items = array_values(array_filter(
            $this->ignoredSlugs(),
            fn (string $item): bool => $item !== $slug
        ));

        SystemSetting::setSetting(self::IGNORE_KEY, json_encode($items), 'json');
    }
}
