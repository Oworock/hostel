@extends('layouts.app')

@section('title', 'Register')

@section('content')
@php
    $registrationFields = json_decode(\App\Models\SystemSetting::getSetting('registration_fields_json', ''), true);
    $registrationRequiredFields = json_decode(\App\Models\SystemSetting::getSetting('registration_required_fields_json', ''), true);
    $registrationCustomFields = json_decode(\App\Models\SystemSetting::getSetting('registration_custom_fields_json', ''), true);
    $registrationSchoolOptions = json_decode(\App\Models\SystemSetting::getSetting('registration_school_options_json', ''), true);
    $registrationFields = is_array($registrationFields) ? $registrationFields : ['phone'];
    $registrationRequiredFields = is_array($registrationRequiredFields) ? $registrationRequiredFields : [];
    $registrationCustomFields = is_array($registrationCustomFields) ? $registrationCustomFields : [];
    $registrationSchoolOptions = collect(is_array($registrationSchoolOptions) ? $registrationSchoolOptions : [])->map(fn ($school) => trim((string) $school))->filter()->unique()->values();
    $referralCode = strtoupper(trim((string) request('ref', session('referral_code', old('referral_code', '')))));
@endphp
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <div class="mb-6">
            <x-system-auth-logo />
        </div>

        <h2 class="text-center text-3xl font-bold text-gray-900 mb-6">Create account</h2>

        @if($referralCode !== '')
            <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-800">
                Referral applied: <span class="font-semibold">{{ $referralCode }}</span>
            </div>
        @endif
        
        <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="referral_code" value="{{ $referralCode }}">
            <input type="hidden" name="form_started_at" value="{{ time() }}">
            <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('first_name') border-red-500 @enderror"
                           required>
                    @error('first_name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('last_name') border-red-500 @enderror"
                           required>
                    @error('last_name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                       required>
                @error('email')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
            
            @if(in_array('phone', $registrationFields, true))
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone @if(!in_array('phone', $registrationRequiredFields, true))(optional)@endif</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-500 @enderror"
                           @if(in_array('phone', $registrationRequiredFields, true)) required @endif>
                    @error('phone')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            @if(in_array('id_number', $registrationFields, true))
                <div>
                    <label for="id_number" class="block text-sm font-medium text-gray-700">ID Number @if(!in_array('id_number', $registrationRequiredFields, true))(optional)@endif</label>
                    <input type="text" id="id_number" name="id_number" value="{{ old('id_number') }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('id_number') border-red-500 @enderror"
                           @if(in_array('id_number', $registrationRequiredFields, true)) required @endif>
                    @error('id_number')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            @if(in_array('address', $registrationFields, true))
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700">Address @if(!in_array('address', $registrationRequiredFields, true))(optional)@endif</label>
                    <input type="text" id="address" name="address" value="{{ old('address') }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('address') border-red-500 @enderror"
                           @if(in_array('address', $registrationRequiredFields, true)) required @endif>
                    @error('address')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            @if(in_array('guardian_name', $registrationFields, true))
                <div>
                    <label for="guardian_name" class="block text-sm font-medium text-gray-700">Guardian Name @if(!in_array('guardian_name', $registrationRequiredFields, true))(optional)@endif</label>
                    <input type="text" id="guardian_name" name="guardian_name" value="{{ old('guardian_name') }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('guardian_name') border-red-500 @enderror"
                           @if(in_array('guardian_name', $registrationRequiredFields, true)) required @endif>
                    @error('guardian_name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            @if(in_array('guardian_phone', $registrationFields, true))
                <div>
                    <label for="guardian_phone" class="block text-sm font-medium text-gray-700">Guardian Phone @if(!in_array('guardian_phone', $registrationRequiredFields, true))(optional)@endif</label>
                    <input type="tel" id="guardian_phone" name="guardian_phone" value="{{ old('guardian_phone') }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('guardian_phone') border-red-500 @enderror"
                           @if(in_array('guardian_phone', $registrationRequiredFields, true)) required @endif>
                    @error('guardian_phone')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            @if($registrationSchoolOptions->isNotEmpty())
                <div>
                    <label for="school" class="block text-sm font-medium text-gray-700">School</label>
                    <select id="school" name="school"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('school') border-red-500 @enderror"
                            required>
                        <option value="">Select School</option>
                        @foreach($registrationSchoolOptions as $school)
                            <option value="{{ $school }}" @selected(old('school') === $school)>{{ $school }}</option>
                        @endforeach
                    </select>
                    @error('school')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            @foreach($registrationCustomFields as $field)
                @php
                    $fieldName = $field['name'] ?? null;
                    $fieldLabel = $field['label'] ?? $fieldName;
                    $fieldType = $field['type'] ?? 'text';
                    $fieldPlaceholder = $field['placeholder'] ?? '';
                    $fieldRequired = (bool) ($field['required'] ?? false);
                @endphp
                @if($fieldName)
                    <div>
                        <label for="{{ $fieldName }}" class="block text-sm font-medium text-gray-700">
                            {{ $fieldLabel }} @if(!$fieldRequired)(optional)@endif
                        </label>
                        @if($fieldType === 'upload')
                            <input type="file" id="{{ $fieldName }}" name="{{ $fieldName }}" accept="image/*"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error($fieldName) border-red-500 @enderror"
                                   @if($fieldRequired) required @endif>
                            <p class="mt-1 text-xs text-gray-500">Image files only.</p>
                        @else
                            <input type="{{ $fieldType }}" id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ old($fieldName) }}"
                                   placeholder="{{ $fieldPlaceholder }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error($fieldName) border-red-500 @enderror"
                                   @if($fieldRequired) required @endif>
                        @endif
                        @error($fieldName)
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            @endforeach
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" name="password" 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror"
                       required>
                @error('password')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                       required>
            </div>
            
            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 font-medium">
                Create account
            </button>
        </form>
        
        <p class="mt-6 text-center text-sm text-gray-600">
            Already have an account?
            <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-500 font-medium">Sign in here</a>
        </p>
    </div>
</div>
@endsection
