@extends('layouts.app')

@section('title', 'General Settings')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-4xl font-bold text-gray-900 mb-8">General Settings</h1>

    <div class="bg-white rounded-lg shadow-md p-8">
        <form method="POST" action="{{ route('admin.settings.general.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="app_name" class="block text-sm font-medium text-gray-700 mb-1">
                        Application Name
                    </label>
                    <input type="text" id="app_name" name="app_name" 
                           value="{{ old('app_name', $settings['app_name'] ?? 'Hostel Manager') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('app_name') border-red-500 @enderror">
                    @error('app_name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">
                        Currency Code
                    </label>
                    <input type="text" id="currency" name="currency" 
                           value="{{ old('currency', $settings['currency'] ?? 'USD') }}"
                           placeholder="USD, NGN, GHS, etc"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('currency') border-red-500 @enderror"
                           maxlength="3">
                    @error('currency')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="app_description" class="block text-sm font-medium text-gray-700 mb-1">
                    Description
                </label>
                <textarea id="app_description" name="app_description" rows="4"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('app_description') border-red-500 @enderror">{{ old('app_description', $settings['app_description'] ?? '') }}</textarea>
                @error('app_description')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="primary_color" class="block text-sm font-medium text-gray-700 mb-1">
                        Primary Color
                    </label>
                    <div class="flex items-center space-x-2">
                        <input type="color" id="primary_color" name="primary_color" 
                               value="{{ old('primary_color', $settings['primary_color'] ?? '#2563eb') }}"
                               class="h-12 w-20 border border-gray-300 rounded cursor-pointer">
                        <input type="text" name="primary_color" 
                               value="{{ old('primary_color', $settings['primary_color'] ?? '#2563eb') }}"
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    @error('primary_color')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="secondary_color" class="block text-sm font-medium text-gray-700 mb-1">
                        Secondary Color
                    </label>
                    <div class="flex items-center space-x-2">
                        <input type="color" id="secondary_color" name="secondary_color" 
                               value="{{ old('secondary_color', $settings['secondary_color'] ?? '#1e40af') }}"
                               class="h-12 w-20 border border-gray-300 rounded cursor-pointer">
                        <input type="text" name="secondary_color" 
                               value="{{ old('secondary_color', $settings['secondary_color'] ?? '#1e40af') }}"
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    @error('secondary_color')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="timezone" class="block text-sm font-medium text-gray-700 mb-1">
                        Timezone
                    </label>
                    <select id="timezone" name="timezone" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('timezone') border-red-500 @enderror">
                        @foreach (timezone_identifiers_list() as $tz)
                            <option value="{{ $tz }}" @selected(old('timezone', $settings['timezone'] ?? 'UTC') === $tz)>
                                {{ $tz }}
                            </option>
                        @endforeach
                    </select>
                    @error('timezone')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="support_email" class="block text-sm font-medium text-gray-700 mb-1">
                        Support Email
                    </label>
                    <input type="email" id="support_email" name="support_email" 
                           value="{{ old('support_email', $settings['support_email'] ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('support_email') border-red-500 @enderror">
                    @error('support_email')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center space-x-4 pt-6 border-t">
                <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 font-medium">
                    Save Changes
                </button>
                <a href="{{ route('admin.settings.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
