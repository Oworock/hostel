<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentIdCardService
{
    public function resolveActiveBooking(User $student): ?Booking
    {
        $today = now()->toDateString();

        $booking = $student->bookings()
            ->with(['room.hostel', 'bed', 'payments'])
            ->where('status', 'approved')
            ->whereDate('check_in_date', '<=', $today)
            ->where(function ($q) use ($today): void {
                $q->whereNull('check_out_date')
                    ->orWhereDate('check_out_date', '>=', $today);
            })
            ->latest('check_in_date')
            ->first();

        if (!$booking || !$booking->isFullyPaid()) {
            return null;
        }

        return $booking;
    }

    public function buildSvg(User $student, Booking $booking): string
    {
        $appName = e((string) get_setting('app_name', config('app.name', 'Hostel System')));
        $logoDataUri = e($this->resolveLogoDataUri());
        $logoBlock = $logoDataUri !== ''
            ? '<image href="' . $logoDataUri . '" x="528" y="32" width="170" height="42" preserveAspectRatio="xMidYMid meet" />'
            : '';
        $studentName = e((string) $student->name);
        $email = e((string) $student->email);
        $phone = e((string) ($student->phone ?: 'N/A'));
        $idNumber = e((string) ($student->id_number ?: ('STD-' . str_pad((string) $student->id, 6, '0', STR_PAD_LEFT))));
        $hostel = e((string) ($booking->room?->hostel?->name ?: 'N/A'));
        $room = e((string) ($booking->room?->room_number ?: 'N/A'));
        $bed = e((string) ($booking->bed?->name ?: 'N/A'));
        $validFrom = e((string) Carbon::parse($booking->check_in_date)->format('Y-m-d'));
        $validUntil = e((string) (optional($booking->check_out_date)?->format('Y-m-d') ?: 'N/A'));
        $issuedAt = e(now()->format('Y-m-d H:i'));

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="760" height="420" viewBox="0 0 760 420" shape-rendering="geometricPrecision" text-rendering="geometricPrecision">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#0f172a" />
      <stop offset="100%" stop-color="#1d4ed8" />
    </linearGradient>
  </defs>
  <rect width="760" height="420" fill="#e2e8f0"/>
  <rect x="22" y="22" width="716" height="376" rx="18" fill="url(#bg)"/>
  {$logoBlock}
  <text x="55" y="70" fill="#e2e8f0" font-family="Arial, Helvetica, sans-serif" font-size="24" font-weight="700">{$appName}</text>
  <text x="55" y="102" fill="#93c5fd" font-family="Arial, Helvetica, sans-serif" font-size="16" font-weight="600">STUDENT ID CARD</text>

  <text x="55" y="160" fill="#ffffff" font-family="Arial, Helvetica, sans-serif" font-size="30" font-weight="700">{$studentName}</text>
  <text x="55" y="195" fill="#cbd5e1" font-family="Arial, Helvetica, sans-serif" font-size="15" font-weight="500">{$email}</text>

  <text x="55" y="252" fill="#93c5fd" font-family="Arial, Helvetica, sans-serif" font-size="14" font-weight="600">Student ID</text>
  <text x="190" y="252" fill="#ffffff" font-family="Arial, Helvetica, sans-serif" font-size="14" font-weight="600">{$idNumber}</text>
  <text x="55" y="280" fill="#93c5fd" font-family="Arial, Helvetica, sans-serif" font-size="14" font-weight="600">Phone</text>
  <text x="190" y="280" fill="#ffffff" font-family="Arial, Helvetica, sans-serif" font-size="14" font-weight="600">{$phone}</text>
  <text x="55" y="308" fill="#93c5fd" font-family="Arial, Helvetica, sans-serif" font-size="14" font-weight="600">Hostel / Room / Bed</text>
  <text x="190" y="308" fill="#ffffff" font-family="Arial, Helvetica, sans-serif" font-size="14" font-weight="600">{$hostel} / {$room} / {$bed}</text>
  <text x="55" y="336" fill="#93c5fd" font-family="Arial, Helvetica, sans-serif" font-size="14" font-weight="600">Valid From</text>
  <text x="190" y="336" fill="#ffffff" font-family="Arial, Helvetica, sans-serif" font-size="14" font-weight="600">{$validFrom}</text>
  <text x="55" y="364" fill="#93c5fd" font-family="Arial, Helvetica, sans-serif" font-size="14" font-weight="600">Valid Until</text>
  <text x="190" y="364" fill="#ffffff" font-family="Arial, Helvetica, sans-serif" font-size="14" font-weight="600">{$validUntil}</text>

  <rect x="520" y="86" width="170" height="220" rx="14" fill="#1e293b" stroke="#334155"/>
  <text x="605" y="188" text-anchor="middle" fill="#94a3b8" font-family="Arial, Helvetica, sans-serif" font-size="12">ACTIVE BOOKING</text>
  <text x="605" y="208" text-anchor="middle" fill="#38bdf8" font-family="Arial, Helvetica, sans-serif" font-size="13">BOOKING #{$booking->id}</text>
  <text x="520" y="336" fill="#cbd5e1" font-family="Arial, Helvetica, sans-serif" font-size="11">Issued: {$issuedAt}</text>
</svg>
SVG;
    }

    public function generatePngBinary(string $svg, int $scale = 2): string
    {
        if (!extension_loaded('imagick')) {
            throw new \RuntimeException('PNG/PDF export requires Imagick extension. Install Imagick on the server to keep SVG design fidelity.');
        }

        $scale = max(1, min(4, $scale));
        $imagick = new \Imagick();
        $imagick->setBackgroundColor(new \ImagickPixel('transparent'));
        $imagick->readImageBlob($svg);
        $imagick->setImageFormat('png32');
        $imagick->resizeImage(760 * $scale, 420 * $scale, \Imagick::FILTER_LANCZOS, 1);

        return (string) $imagick->getImageBlob();
    }

    public function buildPngDataUri(string $svg, int $scale = 2): string
    {
        $binary = $this->generatePngBinary($svg, $scale);

        return 'data:image/png;base64,' . base64_encode($binary);
    }

    public function buildDownloadFileName(User $student, string $extension): string
    {
        $slug = Str::slug((string) $student->name) ?: ('student-' . $student->id);

        return 'student-id-' . $slug . '.' . $extension;
    }

    private function resolveLogoDataUri(): string
    {
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

        if (str_starts_with($candidate, 'data:')) {
            return $candidate;
        }
        if (str_starts_with($candidate, 'http://') || str_starts_with($candidate, 'https://')) {
            return $candidate;
        }

        $path = Storage::disk('public')->path($this->normalizeStoredPath($candidate));
        return $this->fileToDataUri($path) ?? '';
    }

    private function normalizeStoredPath(string $path): string
    {
        return (string) preg_replace('/^(storage\/|public\/)/', '', ltrim($path, '/'));
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
}
