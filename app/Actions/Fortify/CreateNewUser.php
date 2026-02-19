<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $optionalFields = json_decode(SystemSetting::getSetting('registration_fields_json', ''), true);
        $requiredFields = json_decode(SystemSetting::getSetting('registration_required_fields_json', ''), true);
        $customFields = json_decode(SystemSetting::getSetting('registration_custom_fields_json', ''), true);
        $optionalFields = is_array($optionalFields) ? $optionalFields : ['phone'];
        $requiredFields = is_array($requiredFields) ? $requiredFields : [];
        $customFields = is_array($customFields) ? $customFields : [];

        $optionalMap = [
            'phone' => ['string', 'max:20'],
            'id_number' => ['string', 'max:50'],
            'address' => ['string', 'max:255'],
            'guardian_name' => ['string', 'max:255'],
            'guardian_phone' => ['string', 'max:20'],
        ];

        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ];

        foreach ($optionalMap as $field => $baseRules) {
            if (!in_array($field, $optionalFields, true)) {
                continue;
            }

            $rules[$field] = in_array($field, $requiredFields, true)
                ? array_merge(['required'], $baseRules)
                : array_merge(['nullable'], $baseRules);
        }

        $customFieldRules = [];
        $reservedFields = ['first_name', 'last_name', 'name', 'email', 'password', 'password_confirmation'];
        foreach ($customFields as $field) {
            $name = $field['name'] ?? null;
            if (
                !$name ||
                !preg_match('/^[a-z][a-z0-9_]*$/', $name) ||
                in_array($name, $reservedFields, true)
            ) {
                continue;
            }

            $type = $field['type'] ?? 'text';
            $required = (bool) ($field['required'] ?? false);
            $base = match ($type) {
                'email' => ['email', 'max:255'],
                'tel' => ['string', 'max:20'],
                'number' => ['numeric'],
                'date' => ['date'],
                'upload' => ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
                default => ['string', 'max:255'],
            };

            $customFieldRules[$name] = array_merge([$required ? 'required' : 'nullable'], $base);
            $rules[$name] = $customFieldRules[$name];
        }

        Validator::make($input, $rules)->validate();

        $payload = [
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'name' => trim($input['first_name'] . ' ' . $input['last_name']),
            'email' => $input['email'],
            'password' => $input['password'],
            'role' => 'student',
            'is_active' => true,
        ];

        foreach (array_keys($optionalMap) as $field) {
            if (in_array($field, $optionalFields, true)) {
                $payload[$field] = $input[$field] ?? null;
            }
        }

        $extraData = [];
        $customFieldMeta = collect($customFields)
            ->filter(fn ($field) => isset($field['name']) && is_string($field['name']))
            ->keyBy(fn ($field) => (string) $field['name']);

        foreach (array_keys($customFieldRules) as $fieldName) {
            $type = (string) ($customFieldMeta->get($fieldName)['type'] ?? 'text');
            $value = $input[$fieldName] ?? null;

            if ($type === 'upload' && $value instanceof UploadedFile) {
                $value = $value->store('registration-uploads/' . now()->format('Y/m'), 'public');
            }

            $extraData[$fieldName] = $value;
        }
        if (!empty($extraData)) {
            $payload['extra_data'] = $extraData;
        }

        return User::create($payload);
    }
}
