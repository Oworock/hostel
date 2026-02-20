<?php

namespace App\Services;

use App\Models\StaffMember;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StaffIdCardService
{
    public function generate(StaffMember $staff): string
    {
        $svg = $this->buildSvg($staff, $this->resolveStaffPhotoUri($staff));
        $fileName = 'staff-id-cards/' . ($staff->id ?: Str::random(8)) . '-' . Str::random(10) . '.svg';
        Storage::disk('public')->put($fileName, $svg);

        return $fileName;
    }

    public function generatePng(StaffMember $staff): string
    {
        $svg = $this->buildSvg($staff, $this->resolveStaffPhotoUri($staff));
        $scale = $this->pngScaleMultiplier();

        $binary = null;
        if (extension_loaded('imagick')) {
            $binary = $this->rasterizeSvgWithImagick($svg, 760 * $scale, 420 * $scale);
        }
        if (!is_string($binary) && extension_loaded('gd')) {
            $binary = $this->renderPngWithGdFallback($staff, $scale);
        }
        if (!is_string($binary)) {
            throw new \RuntimeException('PNG export requires Imagick (recommended) or GD extension.');
        }

        $fileName = 'staff-id-cards/' . ($staff->id ?: Str::random(8)) . '-' . Str::random(10) . '.png';
        Storage::disk('public')->put($fileName, $binary);

        return $fileName;
    }

    private function buildSvg(StaffMember $staff, string $photoUrl): string
    {
        $logoUrl = e($this->resolveLogoUrl());
        $templateUrl = e($this->resolveTemplateUrl());

        $backgroundBlock = $templateUrl !== ''
            ? '<image href="' . $templateUrl . '" x="0" y="0" width="760" height="420" preserveAspectRatio="none" />'
            : '<rect width="760" height="420" fill="#e2e8f0"/><rect x="25" y="25" width="710" height="370" rx="18" fill="url(#bg)" />';

        $layoutBlock = '';
        $logoBlock = '';
        $photoBlock = '';
        foreach ($this->idCardLayoutRows() as $row) {
            $key = (string) ($row['key'] ?? '');
            if ($key === '') {
                continue;
            }

            if ($key === '__logo') {
                if ($logoUrl !== '') {
                    $logoBlock .= '<image href="' . $logoUrl . '" x="' . (int) ($row['x'] ?? 545) . '" y="' . (int) ($row['y'] ?? 32) . '" width="' . max(24, (int) ($row['width'] ?? 170)) . '" height="' . max(24, (int) ($row['height'] ?? 34)) . '" preserveAspectRatio="xMidYMid meet" />';
                }
                continue;
            }

            if ($key === '__photo') {
                $px = (int) ($row['x'] ?? 545);
                $py = (int) ($row['y'] ?? 75);
                $pw = max(24, (int) ($row['width'] ?? 130));
                $ph = max(24, (int) ($row['height'] ?? 160));
                $clipId = 'clip-photo-' . $px . '-' . $py . '-' . $pw . '-' . $ph;
                if ($photoUrl !== '') {
                    $photoBlock .= '<defs><clipPath id="' . e($clipId) . '"><rect x="' . $px . '" y="' . $py . '" width="' . $pw . '" height="' . $ph . '" rx="12"/></clipPath></defs>';
                    $photoBlock .= '<image href="' . e($photoUrl) . '" x="' . $px . '" y="' . $py . '" width="' . $pw . '" height="' . $ph . '" preserveAspectRatio="xMidYMid slice" clip-path="url(#' . e($clipId) . ')" />';
                } else {
                    $photoBlock .= '<rect x="' . $px . '" y="' . $py . '" width="' . $pw . '" height="' . $ph . '" fill="#cbd5e1" rx="12"/><text x="' . ($px + (int) floor($pw / 2)) . '" y="' . ($py + (int) floor($ph / 2)) . '" text-anchor="middle" fill="#334155" font-size="16" font-family="Arial">NO PHOTO</text>';
                }
                continue;
            }

            $value = e($this->layoutValue($staff, $key));
            if ($value === '') {
                continue;
            }
            $weight = (int) ($row['weight'] ?? 400);
            $layoutBlock .= '<text x="' . (int) ($row['x'] ?? 55) . '" y="' . (int) ($row['y'] ?? 160) . '" fill="' . e((string) ($row['color'] ?? '#ffffff')) . '" font-family="Arial, Helvetica, sans-serif" font-size="' . (int) ($row['size'] ?? 14) . '" font-weight="' . $weight . '">' . $value . '</text>';
        }

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="760" height="420" viewBox="0 0 760 420" shape-rendering="geometricPrecision" text-rendering="geometricPrecision">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#0f172a" />
      <stop offset="100%" stop-color="#1e3a8a" />
    </linearGradient>
  </defs>
  {$backgroundBlock}
  {$photoBlock}
  {$logoBlock}
  {$layoutBlock}
</svg>
SVG;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function idCardLayoutRows(): array
    {
        $rows = json_decode((string) get_setting('staff_payroll_id_card_layout_json', '[]'), true);
        $defaults = $this->defaultIdCardLayoutRows();
        if (!is_array($rows) || $rows === []) {
            return $defaults;
        }

        $normalized = array_values(array_filter($rows, static fn (mixed $row): bool => is_array($row)));
        $existing = collect($normalized)->map(fn (array $row): string => (string) ($row['key'] ?? ''))->filter()->all();
        foreach ($defaults as $row) {
            if (!in_array((string) $row['key'], $existing, true)) {
                $normalized[] = $row;
            }
        }

        return array_values($normalized);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function defaultIdCardLayoutRows(): array
    {
        return [
            ['key' => '__logo', 'x' => 545, 'y' => 32, 'width' => 170, 'height' => 34, 'locked' => false],
            ['key' => '__photo', 'x' => 545, 'y' => 75, 'width' => 130, 'height' => 160, 'locked' => false],
            ['key' => '__app_name', 'x' => 55, 'y' => 72, 'size' => 24, 'color' => '#e2e8f0', 'weight' => 700, 'locked' => false],
            ['key' => '__card_title', 'x' => 55, 'y' => 105, 'size' => 16, 'color' => '#93c5fd', 'weight' => 600, 'locked' => false],
            ['key' => 'full_name', 'x' => 55, 'y' => 162, 'size' => 30, 'color' => '#ffffff', 'weight' => 700, 'locked' => false],
            ['key' => 'job_title', 'x' => 55, 'y' => 198, 'size' => 16, 'color' => '#cbd5e1', 'weight' => 500, 'locked' => false],
            ['key' => '__label_staff_code', 'x' => 55, 'y' => 252, 'size' => 14, 'color' => '#93c5fd', 'weight' => 600, 'locked' => false],
            ['key' => '__label_department', 'x' => 55, 'y' => 280, 'size' => 14, 'color' => '#93c5fd', 'weight' => 600, 'locked' => false],
            ['key' => '__label_email', 'x' => 55, 'y' => 308, 'size' => 14, 'color' => '#93c5fd', 'weight' => 600, 'locked' => false],
            ['key' => '__label_phone', 'x' => 55, 'y' => 336, 'size' => 14, 'color' => '#93c5fd', 'weight' => 600, 'locked' => false],
            ['key' => '__label_joined', 'x' => 55, 'y' => 364, 'size' => 14, 'color' => '#93c5fd', 'weight' => 600, 'locked' => false],
            ['key' => 'employee_code', 'x' => 185, 'y' => 252, 'size' => 14, 'color' => '#ffffff', 'weight' => 600, 'locked' => false],
            ['key' => 'department', 'x' => 185, 'y' => 280, 'size' => 14, 'color' => '#ffffff', 'weight' => 600, 'locked' => false],
            ['key' => 'email', 'x' => 185, 'y' => 308, 'size' => 14, 'color' => '#ffffff', 'weight' => 600, 'locked' => false],
            ['key' => 'phone', 'x' => 185, 'y' => 336, 'size' => 14, 'color' => '#ffffff', 'weight' => 600, 'locked' => false],
            ['key' => 'joined_on', 'x' => 185, 'y' => 364, 'size' => 14, 'color' => '#ffffff', 'weight' => 600, 'locked' => false],
        ];
    }

    private function layoutValue(StaffMember $staff, string $key): string
    {
        if (str_starts_with($key, 'text:')) {
            return trim(substr($key, 5));
        }

        return match ($key) {
            '__app_name' => (string) $this->resolveBrandName(),
            '__card_title' => (string) get_setting('staff_payroll_id_card_title', 'STAFF ID CARD'),
            '__card_subtitle' => (string) get_setting('staff_payroll_id_card_subtitle', ''),
            '__card_footer' => (string) get_setting('staff_payroll_id_card_footer', ''),
            '__label_staff_code' => (string) __('Staff Code'),
            '__label_department' => (string) __('Department'),
            '__label_email' => (string) __('Email'),
            '__label_phone' => (string) __('Phone'),
            '__label_joined' => (string) __('Joined'),
            'full_name' => (string) $staff->full_name,
            'job_title' => (string) ($staff->job_title ?: ($staff->source_role ? ucfirst((string) $staff->source_role) : 'N/A')),
            'employee_code' => (string) ($staff->employee_code ?: 'STAFF-' . str_pad((string) $staff->id, 5, '0', STR_PAD_LEFT)),
            'department' => (string) ($staff->is_general_staff ? __('All Hostels') : ($staff->department ?: 'N/A')),
            'category' => (string) ($staff->category ?: 'N/A'),
            'email' => (string) $staff->email,
            'phone' => (string) ($staff->phone ?: 'N/A'),
            'joined_on' => (string) ($staff->joined_on?->format('Y-m-d') ?: 'N/A'),
            'hostel' => (string) ($staff->is_general_staff ? __('All Hostels') : ($staff->assignedHostel?->name ?: 'N/A')),
            default => '',
        };
    }

    private function drawBackground(\GdImage $image, int $w, int $h): void
    {
        $templatePath = $this->resolveTemplateStoragePath();
        if ($templatePath) {
            $raw = @file_get_contents($templatePath);
            $source = $raw !== false ? @imagecreatefromstring($raw) : false;
            if ($source !== false) {
                imagecopyresampled($image, $source, 0, 0, 0, 0, $w, $h, imagesx($source), imagesy($source));
                imagedestroy($source);

                return;
            }
        }

        $bg = imagecolorallocate($image, 226, 232, 240);
        $card = imagecolorallocate($image, 15, 23, 42);
        $sx = $w / 760;
        $sy = $h / 420;
        imagefilledrectangle($image, 0, 0, $w, $h, $bg);
        imagefilledrectangle(
            $image,
            (int) round(25 * $sx),
            (int) round(25 * $sy),
            (int) round(735 * $sx),
            (int) round(395 * $sy),
            $card
        );
    }

    private function hexToColor(\GdImage $image, string $hex): int
    {
        $hex = ltrim(trim($hex), '#');
        if (strlen($hex) !== 6) {
            $hex = 'ffffff';
        }

        return imagecolorallocate($image, hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
    }

    private function sizeToFont(int $size): int
    {
        return match (true) {
            $size >= 30 => 5,
            $size >= 18 => 4,
            $size >= 14 => 3,
            default => 2,
        };
    }

    private function drawPhoto(\GdImage $image, StaffMember $staff, int $x1, int $y1, int $x2, int $y2, int $fallbackColor, int $fallbackTextColor): void
    {
        if (empty($staff->profile_image)) {
            imagefilledrectangle($image, $x1, $y1, $x2, $y2, $fallbackColor);
            imagestring($image, 3, $x1 + 38, $y1 + 75, 'NO PHOTO', $fallbackTextColor);

            return;
        }

        $path = Storage::disk('public')->path((string) $staff->profile_image);
        if (!is_file($path)) {
            imagefilledrectangle($image, $x1, $y1, $x2, $y2, $fallbackColor);
            imagestring($image, 3, $x1 + 38, $y1 + 75, 'NO PHOTO', $fallbackTextColor);

            return;
        }

        $raw = @file_get_contents($path);
        $source = $raw !== false ? @imagecreatefromstring($raw) : false;
        if ($source === false) {
            imagefilledrectangle($image, $x1, $y1, $x2, $y2, $fallbackColor);
            imagestring($image, 3, $x1 + 38, $y1 + 75, 'NO PHOTO', $fallbackTextColor);

            return;
        }

        imagecopyresampled($image, $source, $x1, $y1, 0, 0, $x2 - $x1, $y2 - $y1, imagesx($source), imagesy($source));
        imagedestroy($source);
    }

    private function truncate(string $value, int $limit): string
    {
        return Str::limit(trim($value), $limit, '...');
    }

    private function resolveLogoUrl(): string
    {
        $customEnabled = filter_var(get_setting('staff_payroll_id_card_use_custom_brand', false), FILTER_VALIDATE_BOOL);
        if ($customEnabled) {
            $customLogo = trim((string) get_setting('staff_payroll_id_card_brand_logo', ''));
            if ($customLogo !== '') {
                $normalized = $this->normalizeStoredPath($customLogo);
                $dataUri = $this->fileToDataUri(Storage::disk('public')->path($normalized));
                if ($dataUri !== null) {
                    return $dataUri;
                }

                return asset('storage/' . $normalized);
            }
        }

        $candidate = trim((string) get_setting('global_header_logo_light', ''));
        if ($candidate === '') {
            $candidate = trim((string) get_setting('global_header_logo', ''));
        }
        if ($candidate === '') {
            $candidate = trim((string) get_setting('app_logo', ''));
        }
        if ($candidate === '') {
            return '';
        }
        if (str_starts_with($candidate, 'http://') || str_starts_with($candidate, 'https://') || str_starts_with($candidate, 'data:')) {
            return $candidate;
        }

        $normalized = $this->normalizeStoredPath($candidate);
        $dataUri = $this->fileToDataUri(Storage::disk('public')->path($normalized));
        if ($dataUri !== null) {
            return $dataUri;
        }

        return asset('storage/' . $normalized);
    }

    private function resolveLogoStoragePath(): ?string
    {
        $customEnabled = filter_var(get_setting('staff_payroll_id_card_use_custom_brand', false), FILTER_VALIDATE_BOOL);
        if ($customEnabled) {
            $customLogo = trim((string) get_setting('staff_payroll_id_card_brand_logo', ''));
            if ($customLogo !== '') {
                $path = Storage::disk('public')->path($this->normalizeStoredPath($customLogo));

                return is_file($path) ? $path : null;
            }
        }

        $candidate = trim((string) get_setting('global_header_logo_light', ''));
        if ($candidate === '') {
            $candidate = trim((string) get_setting('global_header_logo', ''));
        }
        if ($candidate === '') {
            $candidate = trim((string) get_setting('app_logo', ''));
        }
        if ($candidate === '' || str_starts_with($candidate, 'http://') || str_starts_with($candidate, 'https://') || str_starts_with($candidate, 'data:')) {
            return null;
        }

        $path = Storage::disk('public')->path($this->normalizeStoredPath($candidate));

        return is_file($path) ? $path : null;
    }

    private function drawLogoOnPng(\GdImage $image, int $x, int $y, int $w, int $h): void
    {
        $path = $this->resolveLogoStoragePath();
        if ($path === null) {
            return;
        }

        $raw = @file_get_contents($path);
        $logo = $raw !== false ? @imagecreatefromstring($raw) : false;
        if ($logo === false) {
            return;
        }

        imagecopyresampled($image, $logo, $x, $y, 0, 0, $w, $h, imagesx($logo), imagesy($logo));
        imagedestroy($logo);
    }

    private function resolveTemplateUrl(): string
    {
        $candidate = trim((string) get_setting('staff_payroll_id_card_background_template', ''));
        if ($candidate === '') {
            return '';
        }
        if (str_starts_with($candidate, 'http://') || str_starts_with($candidate, 'https://') || str_starts_with($candidate, 'data:')) {
            return $candidate;
        }

        $normalized = $this->normalizeStoredPath($candidate);
        $dataUri = $this->fileToDataUri(Storage::disk('public')->path($normalized));
        if ($dataUri !== null) {
            return $dataUri;
        }

        return asset('storage/' . $normalized);
    }

    private function resolveTemplateStoragePath(): ?string
    {
        $candidate = trim((string) get_setting('staff_payroll_id_card_background_template', ''));
        if ($candidate === '' || str_starts_with($candidate, 'http://') || str_starts_with($candidate, 'https://') || str_starts_with($candidate, 'data:')) {
            return null;
        }
        $path = Storage::disk('public')->path($this->normalizeStoredPath($candidate));

        return is_file($path) ? $path : null;
    }

    private function resolveBrandName(): string
    {
        $customEnabled = filter_var(get_setting('staff_payroll_id_card_use_custom_brand', false), FILTER_VALIDATE_BOOL);
        $customName = trim((string) get_setting('staff_payroll_id_card_brand_name', ''));
        if ($customEnabled && $customName !== '') {
            return $customName;
        }

        return (string) get_setting('app_name', config('app.name', 'Hostel System'));
    }

    private function normalizeStoredPath(string $path): string
    {
        return (string) preg_replace('/^(storage\/|public\/)/', '', ltrim($path, '/'));
    }

    private function resolveStaffPhotoUri(StaffMember $staff): string
    {
        if (empty($staff->profile_image)) {
            return '';
        }

        $path = Storage::disk('public')->path((string) $staff->profile_image);
        return $this->fileToDataUri($path) ?? '';
    }

    private function fileToDataUri(string $path): ?string
    {
        if (!is_file($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'application/octet-stream';
        $raw = @file_get_contents($path);
        if ($raw === false) {
            return null;
        }

        return 'data:' . $mime . ';base64,' . base64_encode($raw);
    }

    private function rasterizeSvgWithImagick(string $svg, int $width, int $height): ?string
    {
        try {
            $imagick = new \Imagick();
            $imagick->setBackgroundColor(new \ImagickPixel('transparent'));
            $imagick->setResolution(300, 300);
            $imagick->readImageBlob($svg);
            $imagick->setImageFormat('png32');
            $imagick->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1, true);

            $blob = $imagick->getImageBlob();
            $imagick->clear();
            $imagick->destroy();

            return is_string($blob) ? $blob : null;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    private function renderPngWithGdFallback(StaffMember $staff, int $scale = 2): ?string
    {
        $w = 760 * $scale;
        $h = 420 * $scale;
        $image = imagecreatetruecolor($w, $h);
        if ($image === false) {
            return null;
        }

        imageantialias($image, true);
        $this->drawBackground($image, $w, $h);

        $muted = imagecolorallocate($image, 203, 213, 225);
        $photoBg = imagecolorallocate($image, 203, 213, 225);
        $photoText = imagecolorallocate($image, 51, 65, 85);

        foreach ($this->idCardLayoutRows() as $row) {
            $key = (string) ($row['key'] ?? '');
            if ($key === '') {
                continue;
            }

            if ($key === '__logo') {
                $this->drawLogoOnPng(
                    $image,
                    ((int) ($row['x'] ?? 545)) * $scale,
                    ((int) ($row['y'] ?? 32)) * $scale,
                    max(24, (int) ($row['width'] ?? 170)) * $scale,
                    max(24, (int) ($row['height'] ?? 34)) * $scale
                );
                continue;
            }

            if ($key === '__photo') {
                $x = ((int) ($row['x'] ?? 545)) * $scale;
                $y = ((int) ($row['y'] ?? 75)) * $scale;
                $w2 = max(24, (int) ($row['width'] ?? 130)) * $scale;
                $h2 = max(24, (int) ($row['height'] ?? 160)) * $scale;
                $this->drawPhoto($image, $staff, $x, $y, $x + $w2, $y + $h2, $photoBg, $photoText);
                continue;
            }

            $value = $this->layoutValue($staff, $key);
            if ($value === '') {
                continue;
            }

            $x = ((int) ($row['x'] ?? 0)) * $scale;
            $y = ((int) ($row['y'] ?? 0)) * $scale;
            $size = (int) ($row['size'] ?? 14);
            $color = $this->hexToColor($image, (string) ($row['color'] ?? '#ffffff'));
            imagestring($image, $this->sizeToFont($size), $x, $y, $this->truncate($value, 60), $color);
        }

        ob_start();
        imagepng($image);
        $binary = ob_get_clean();
        imagedestroy($image);

        return is_string($binary) ? $binary : null;
    }

    private function pngScaleMultiplier(): int
    {
        $raw = (string) get_setting('staff_payroll_id_card_png_scale', '2');

        return $raw === '3' ? 3 : 2;
    }
}
