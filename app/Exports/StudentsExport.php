<?php

namespace App\Exports;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentsExport implements FromCollection, WithHeadings, WithMapping
{
    /** @var array<int, array{name: string, label: string, type: string, placeholder: string, required: bool}> */
    private array $customFields = [];

    public function __construct()
    {
        $fields = json_decode((string) SystemSetting::getSetting('registration_custom_fields_json', '[]'), true);
        $this->customFields = collect(is_array($fields) ? $fields : [])
            ->filter(fn ($field) => is_array($field) && !empty($field['name']))
            ->map(function (array $field): array {
                return [
                    'name' => (string) $field['name'],
                    'label' => (string) ($field['label'] ?? $field['name']),
                    'type' => (string) ($field['type'] ?? 'text'),
                    'placeholder' => (string) ($field['placeholder'] ?? ''),
                    'required' => (bool) ($field['required'] ?? false),
                ];
            })
            ->values()
            ->all();
    }

    public function collection(): Collection
    {
        return User::query()
            ->where('role', 'student')
            ->with('hostel')
            ->orderBy('id')
            ->get();
    }

    public function headings(): array
    {
        $headings = [
            'id',
            'first_name',
            'last_name',
            'name',
            'email',
            'phone',
            'id_number',
            'address',
            'guardian_name',
            'guardian_phone',
            'hostel_id',
            'hostel_name',
            'is_active',
            'profile_image',
            'extra_data_json',
            'created_at',
            'updated_at',
        ];

        foreach ($this->customFields as $field) {
            $headings[] = 'custom_' . $field['name'];
        }

        return $headings;
    }

    /**
     * @param  \App\Models\User  $student
     */
    public function map($student): array
    {
        $row = [
            $student->id,
            $student->first_name,
            $student->last_name,
            $student->name,
            $student->email,
            $student->phone,
            $student->id_number,
            $student->address,
            $student->guardian_name,
            $student->guardian_phone,
            $student->hostel_id,
            $student->hostel?->name,
            $student->is_active ? 1 : 0,
            $student->profile_image,
            !empty($student->extra_data) ? json_encode($student->extra_data, JSON_UNESCAPED_SLASHES) : '',
            optional($student->created_at)->toDateTimeString(),
            optional($student->updated_at)->toDateTimeString(),
        ];

        $extraData = is_array($student->extra_data) ? $student->extra_data : [];
        foreach ($this->customFields as $field) {
            $row[] = (string) ($extraData[$field['name']] ?? '');
        }

        return $row;
    }
}
