<?php

namespace App\Imports;

use App\Models\Hostel;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToCollection, WithHeadingRow
{
    public int $created = 0;
    public int $updated = 0;
    public int $skipped = 0;

    /** @var array<int, string> */
    private array $customFieldNames = [];

    public function __construct()
    {
        $fields = json_decode((string) SystemSetting::getSetting('registration_custom_fields_json', '[]'), true);
        $this->customFieldNames = collect(is_array($fields) ? $fields : [])
            ->pluck('name')
            ->filter(fn ($name) => is_string($name) && $name !== '')
            ->values()
            ->all();
    }

    public function collection(Collection $rows): void
    {
        $this->validateRequiredHeaders($rows);

        foreach ($rows as $row) {
            $email = trim((string) ($row['email'] ?? ''));
            $firstName = trim((string) ($row['first_name'] ?? ''));
            $lastName = trim((string) ($row['last_name'] ?? ''));
            $phone = $this->nullableTrim($row['phone'] ?? null);
            $name = trim($firstName . ' ' . $lastName);

            if ($email === '' || $firstName === '' || $lastName === '' || $phone === null) {
                $this->skipped++;
                continue;
            }

            $hostelId = $this->resolveHostelId($row);
            $extraData = $this->extractExtraData($row);

            $payload = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'name' => $name,
                'phone' => $phone,
                'id_number' => $this->nullableTrim($row['id_number'] ?? null),
                'address' => $this->nullableTrim($row['address'] ?? null),
                'guardian_name' => $this->nullableTrim($row['guardian_name'] ?? null),
                'guardian_phone' => $this->nullableTrim($row['guardian_phone'] ?? null),
                'hostel_id' => $hostelId,
                'is_active' => $this->toBool($row['is_active'] ?? 1),
                'profile_image' => $this->nullableTrim($row['profile_image'] ?? null),
                'extra_data' => $extraData,
                'role' => 'student',
            ];

            /** @var User|null $existing */
            $existing = User::where('email', $email)->first();
            if ($existing) {
                $payload['is_admin_uploaded'] = $existing->is_admin_uploaded;
                $payload['must_change_password'] = $existing->must_change_password;
                $existing->update($payload);
                $this->updated++;
                continue;
            }

            User::create(array_merge($payload, [
                'email' => $email,
                'password' => Hash::make($lastName),
                'is_admin_uploaded' => true,
                'must_change_password' => true,
                'email_verified_at' => now(),
            ]));
            $this->created++;
        }
    }

    private function validateRequiredHeaders(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            return;
        }

        $first = $rows->first();
        if (!is_array($first)) {
            return;
        }

        $keys = array_keys($first);
        $required = ['email', 'first_name', 'last_name', 'phone'];
        $missing = array_values(array_filter($required, fn ($key) => !in_array($key, $keys, true)));

        if (!empty($missing)) {
            throw new \RuntimeException('Missing required import columns: ' . implode(', ', $missing));
        }
    }

    private function resolveHostelId($row): ?int
    {
        $hostelId = $row['hostel_id'] ?? null;
        if (!empty($hostelId) && is_numeric($hostelId) && Hostel::whereKey((int) $hostelId)->exists()) {
            return (int) $hostelId;
        }

        $hostelName = trim((string) ($row['hostel_name'] ?? ''));
        if ($hostelName === '') {
            return null;
        }

        return Hostel::where('name', $hostelName)->value('id');
    }

    private function normalizeExtraData(string $raw): array
    {
        if (trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function extractExtraData($row): ?array
    {
        $jsonExtra = $this->normalizeExtraData((string) ($row['extra_data_json'] ?? ''));
        $extra = $jsonExtra;

        foreach ($this->customFieldNames as $fieldName) {
            $directValue = $this->nullableTrim($row[$fieldName] ?? null);
            $prefixedValue = $this->nullableTrim($row['custom_' . $fieldName] ?? null);
            $value = $prefixedValue ?? $directValue;

            if ($value !== null) {
                $extra[$fieldName] = $value;
            }
        }

        return empty($extra) ? null : $extra;
    }

    private function nullableTrim($value): ?string
    {
        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }

    private function toBool($value): bool
    {
        $normalized = strtolower(trim((string) $value));
        return in_array($normalized, ['1', 'true', 'yes', 'y', 'on'], true);
    }
}
