@extends('layouts.app')

@section('title', 'SMS Provider Settings')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-4xl font-bold text-gray-900 mb-2">SMS Provider Settings</h1>
    <p class="text-gray-600 mb-8">Configure SMS providers for bulk messaging campaigns</p>

    <div class="grid grid-cols-1 gap-8">
        @foreach($smsProviders as $provider)
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-gradient-to-r from-green-600 to-green-800 p-6 text-white">
                    <h2 class="text-2xl font-bold">{{ $provider->name }}</h2>
                </div>

                <div class="p-8">
                    <form method="POST" action="{{ route('admin.settings.sms.update', $provider) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    API Key
                                </label>
                                <input type="text" name="api_key" 
                                       value="{{ old('api_key', $provider->api_key) }}"
                                       placeholder="Enter {{ $provider->name }} API key"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 @error('api_key') border-red-500 @enderror">
                                @error('api_key')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    API Secret (Optional)
                                </label>
                                <input type="password" name="api_secret" 
                                       value="{{ old('api_secret', $provider->api_secret) }}"
                                       placeholder="Enter {{ $provider->name }} API secret (if required)"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 @error('api_secret') border-red-500 @enderror">
                                @error('api_secret')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Sender ID
                                </label>
                                <input type="text" name="sender_id" 
                                       value="{{ old('sender_id', $provider->sender_id) }}"
                                       placeholder="Your sender ID"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 @error('sender_id') border-red-500 @enderror">
                                @error('sender_id')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Status
                                </label>
                                <div class="flex items-center space-x-4 mt-2">
                                    <label class="flex items-center">
                                        <input type="radio" name="is_active" value="1" 
                                               @checked(old('is_active', $provider->is_active))
                                               class="w-4 h-4 text-green-600">
                                        <span class="ml-2 text-gray-700">Active</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="is_active" value="0" 
                                               @checked(!old('is_active', $provider->is_active))
                                               class="w-4 h-4 text-gray-600">
                                        <span class="ml-2 text-gray-700">Inactive</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Configuration (JSON)
                            </label>
                            <textarea name="config" rows="4"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 font-mono text-sm"
                                      placeholder='{"key": "value"}'>{{ old('config', $provider->config ?? '{}') }}</textarea>
                            <p class="mt-1 text-xs text-gray-600">Additional configuration in JSON format (optional)</p>
                        </div>

                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <p class="text-sm text-green-800">
                                <strong>Documentation Links:</strong>
                                @if($provider->name === 'Twilio')
                                    <a href="https://www.twilio.com/console" target="_blank" class="underline block">Twilio Console</a>
                                @elseif($provider->name === 'Termii')
                                    <a href="https://www.termii.com/dashboard" target="_blank" class="underline block">Termii Dashboard</a>
                                @elseif($provider->name === "Africa's Talking")
                                    <a href="https://africastalking.com/sms/api" target="_blank" class="underline block">Africa's Talking API</a>
                                @endif
                            </p>
                        </div>

                        <div class="flex items-center space-x-4 pt-6 border-t">
                            <button type="submit" class="bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 font-medium">
                                Save {{ $provider->name }} Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
