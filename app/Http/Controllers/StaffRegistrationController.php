<?php

namespace App\Http\Controllers;

use App\Models\Hostel;
use App\Models\StaffMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class StaffRegistrationController extends Controller
{
    public function create(string $token)
    {
        abort_unless($this->isValidRegistrationToken($token), 404);

        abort_unless(Schema::hasTable('staff_members'), 503, __('Staff registration is temporarily unavailable. Please contact admin.'));

        $config = $this->registrationConfig();
        $hostels = [];
        if ($config['show_hostel_selector'] && Schema::hasTable('hostels')) {
            $hostels = Hostel::query()->orderBy('name')->pluck('name', 'id')->all();
        }

        return view('public.staff-register', [
            'token' => $token,
            'config' => $config,
            'hostels' => $hostels,
        ]);
    }

    public function store(Request $request, string $token)
    {
        abort_unless($this->isValidRegistrationToken($token), 404);
        abort_unless(Schema::hasTable('staff_members'), 503, __('Staff registration is temporarily unavailable. Please contact admin.'));

        $config = $this->registrationConfig();

        $rules = [
            'full_name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:160', Rule::unique('staff_members', 'email')],
            'phone' => ['required', 'string', 'max:32'],
        ];

        if ($config['show_department']) {
            $rules['department'] = [$config['require_department'] ? 'required' : 'nullable', 'string', 'max:120'];
            if (!empty($config['department_options'])) {
                $rules['department'][] = Rule::in($config['department_options']);
            }
        }
        if ($config['show_category'] && Schema::hasColumn('staff_members', 'category')) {
            $rules['category'] = [$config['require_category'] ? 'required' : 'nullable', 'string', 'max:120'];
            if (!empty($config['category_options'])) {
                $rules['category'][] = Rule::in($config['category_options']);
            }
        }
        if ($config['show_job_title']) {
            $rules['job_title'] = [$config['require_job_title'] ? 'required' : 'nullable', 'string', 'max:120'];
        }
        if ($config['show_address']) {
            $rules['address'] = [$config['require_address'] ? 'required' : 'nullable', 'string', 'max:500'];
        }
        $hasProfileImageColumn = Schema::hasColumn('staff_members', 'profile_image');
        if ($config['show_profile_image'] && $hasProfileImageColumn) {
            $rules['profile_image'] = [$config['require_profile_image'] ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'];
        }
        if ($config['show_hostel_selector'] && Schema::hasTable('hostels')) {
            $rules['is_general_staff'] = ['nullable', 'boolean'];
            $rules['assigned_hostel_id'] = ['nullable', 'integer', Rule::exists('hostels', 'id')];
        }
        foreach ($config['custom_fields'] as $field) {
            $customKey = (string) ($field['key'] ?? '');
            if ($customKey === '') {
                continue;
            }

            $ruleKey = "custom.{$customKey}";
            $required = !empty($field['required']) ? 'required' : 'nullable';
            $type = (string) ($field['type'] ?? 'text');

            $rules[$ruleKey] = match ($type) {
                'email' => [$required, 'email', 'max:191'],
                'number' => [$required, 'numeric'],
                'date' => [$required, 'date'],
                'textarea' => [$required, 'string', 'max:5000'],
                'select' => [$required, 'string', 'max:191'],
                default => [$required, 'string', 'max:191'],
            };
        }

        $data = $request->validate($rules);

        $profileImage = null;
        if ($config['show_profile_image'] && $hasProfileImageColumn && $request->hasFile('profile_image')) {
            $profileImage = $request->file('profile_image')?->store('staff/passports', 'public');
        }

        $isGeneralStaff = true;
        $assignedHostelId = null;
        if ($config['show_hostel_selector'] && Schema::hasTable('hostels')) {
            $isGeneralStaff = filter_var($data['is_general_staff'] ?? true, FILTER_VALIDATE_BOOL);
            $assignedHostelId = $isGeneralStaff ? null : ($data['assigned_hostel_id'] ?? null);
            if ($config['require_hostel_selector'] && !$isGeneralStaff && empty($assignedHostelId)) {
                return back()
                    ->withInput()
                    ->withErrors(['assigned_hostel_id' => __('Please select a hostel or mark this as general staff.')]);
            }
        }

        $payload = [
            'full_name' => (string) $data['full_name'],
            'email' => (string) $data['email'],
            'phone' => (string) $data['phone'],
            'department' => (string) ($data['department'] ?? ''),
            'category' => (string) ($data['category'] ?? ''),
            'job_title' => (string) ($data['job_title'] ?? ''),
            'address' => (string) ($data['address'] ?? ''),
            'profile_image' => $profileImage,
            'source_role' => 'staff',
            'status' => 'pending',
            'registered_via_link' => true,
            'is_general_staff' => $isGeneralStaff,
            'assigned_hostel_id' => $assignedHostelId,
            'created_by' => null,
        ];
        if (!empty($config['custom_fields'])) {
            $payload['meta'] = [
                'registration_custom' => is_array($data['custom'] ?? null) ? $data['custom'] : [],
            ];
        }

        $allowedColumns = array_flip(Schema::getColumnListing('staff_members'));
        $safePayload = array_intersect_key($payload, $allowedColumns);
        StaffMember::create($safePayload);

        return redirect()
            ->route('staff.register.create', ['token' => $token])
            ->with('success', __('Registration submitted successfully. Admin will review your profile.'));
    }

    private function isValidRegistrationToken(string $token): bool
    {
        $enabled = filter_var(get_setting('staff_payroll_registration_enabled', false), FILTER_VALIDATE_BOOL);
        $expected = (string) get_setting('staff_payroll_registration_token', '');

        return $enabled && $expected !== '' && hash_equals($expected, $token);
    }

    private function registrationConfig(): array
    {
        return [
            'intro' => (string) get_setting('staff_payroll_registration_intro', __('Fill your details below. Your record will be reviewed by the administrator.')),
            'show_department' => filter_var(get_setting('staff_payroll_registration_show_department', true), FILTER_VALIDATE_BOOL),
            'require_department' => filter_var(get_setting('staff_payroll_registration_require_department', false), FILTER_VALIDATE_BOOL),
            'show_job_title' => filter_var(get_setting('staff_payroll_registration_show_job_title', true), FILTER_VALIDATE_BOOL),
            'require_job_title' => filter_var(get_setting('staff_payroll_registration_require_job_title', false), FILTER_VALIDATE_BOOL),
            'show_category' => Schema::hasColumn('staff_members', 'category')
                && filter_var(get_setting('staff_payroll_registration_show_category', true), FILTER_VALIDATE_BOOL),
            'require_category' => filter_var(get_setting('staff_payroll_registration_require_category', false), FILTER_VALIDATE_BOOL),
            'show_address' => filter_var(get_setting('staff_payroll_registration_show_address', true), FILTER_VALIDATE_BOOL),
            'require_address' => filter_var(get_setting('staff_payroll_registration_require_address', false), FILTER_VALIDATE_BOOL),
            'show_profile_image' => filter_var(get_setting('staff_payroll_registration_show_profile_image', true), FILTER_VALIDATE_BOOL),
            'require_profile_image' => filter_var(get_setting('staff_payroll_registration_require_profile_image', false), FILTER_VALIDATE_BOOL),
            'show_hostel_selector' => filter_var(get_setting('staff_payroll_registration_show_hostel_selector', true), FILTER_VALIDATE_BOOL),
            'require_hostel_selector' => filter_var(get_setting('staff_payroll_registration_require_hostel_selector', false), FILTER_VALIDATE_BOOL),
            'label_full_name' => (string) get_setting('staff_payroll_registration_label_full_name', __('Full Name')),
            'label_email' => (string) get_setting('staff_payroll_registration_label_email', __('Email')),
            'label_phone' => (string) get_setting('staff_payroll_registration_label_phone', __('Phone')),
            'label_department' => (string) get_setting('staff_payroll_registration_label_department', __('Department')),
            'label_job_title' => (string) get_setting('staff_payroll_registration_label_job_title', __('Job Title')),
            'label_category' => (string) get_setting('staff_payroll_registration_label_category', __('Category')),
            'label_address' => (string) get_setting('staff_payroll_registration_label_address', __('Address')),
            'label_profile_image' => (string) get_setting('staff_payroll_registration_label_profile_image', __('Passport Photo')),
            'label_assigned_hostel' => (string) get_setting('staff_payroll_registration_label_assigned_hostel', __('Assigned Hostel')),
            'label_general_staff' => (string) get_setting('staff_payroll_registration_label_general_staff', __('I am a general staff (all hostels)')),
            'department_options' => $this->csvOptions((string) get_setting('staff_payroll_departments_csv', '')),
            'category_options' => $this->csvOptions((string) get_setting('staff_payroll_categories_csv', '')),
            'custom_fields' => $this->getCustomFields(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getCustomFields(): array
    {
        $raw = json_decode((string) get_setting('staff_payroll_registration_custom_fields_json', '[]'), true);
        if (!is_array($raw)) {
            return [];
        }

        $fields = [];
        foreach ($raw as $row) {
            if (!is_array($row)) {
                continue;
            }
            $key = strtolower(trim((string) ($row['key'] ?? '')));
            $key = preg_replace('/[^a-z0-9_]/', '_', $key);
            $key = trim((string) $key, '_');
            if ($key === '') {
                continue;
            }

            $type = (string) ($row['type'] ?? 'text');
            if (!in_array($type, ['text', 'textarea', 'email', 'number', 'date', 'select'], true)) {
                $type = 'text';
            }

            $options = [];
            if ($type === 'select') {
                $options = array_values(array_filter(array_map('trim', explode(',', (string) ($row['options'] ?? '')))));
            }

            $fields[] = [
                'key' => $key,
                'label' => (string) ($row['label'] ?? ucfirst(str_replace('_', ' ', $key))),
                'type' => $type,
                'required' => !empty($row['required']),
                'placeholder' => (string) ($row['placeholder'] ?? ''),
                'options' => $options,
            ];
        }

        return $fields;
    }

    /**
     * @return array<int, string>
     */
    private function csvOptions(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}
