@extends('layouts.app')

@section('title', 'Register')

@section('content')
@php
    $registrationFields = json_decode(\App\Models\SystemSetting::getSetting('registration_fields_json', ''), true);
    $registrationRequiredFields = json_decode(\App\Models\SystemSetting::getSetting('registration_required_fields_json', ''), true);
    $registrationCustomFields = json_decode(\App\Models\SystemSetting::getSetting('registration_custom_fields_json', ''), true);
    $registrationFields = is_array($registrationFields) ? $registrationFields : ['phone'];
    $registrationRequiredFields = is_array($registrationRequiredFields) ? $registrationRequiredFields : [];
    $registrationCustomFields = is_array($registrationCustomFields) ? $registrationCustomFields : [];
@endphp
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <h2 class="text-center text-3xl font-bold text-gray-900 mb-6">Create account</h2>
        
        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="form_started_at" value="{{ time() }}">
            <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
            
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                       required>
                @error('name')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
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
                        <input type="{{ $fieldType }}" id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ old($fieldName) }}"
                               placeholder="{{ $fieldPlaceholder }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error($fieldName) border-red-500 @enderror"
                               @if($fieldRequired) required @endif>
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
