@extends('layouts.app')

@section('title', 'Payment Gateway Settings')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-4xl font-bold text-gray-900 mb-2">Payment Gateway Settings</h1>
    <p class="text-gray-600 mb-8">Configure Paystack and Flutterwave for secure payments</p>

    <div class="grid grid-cols-1 gap-8">
        @foreach($paymentGateways as $gateway)
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 p-6 text-white">
                    <h2 class="text-2xl font-bold">{{ $gateway->name }}</h2>
                </div>

                <div class="p-8">
                    <form method="POST" action="{{ route('admin.settings.payment.update', $gateway) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Public Key
                                </label>
                                <input type="text" name="public_key" 
                                       value="{{ old('public_key', $gateway->public_key) }}"
                                       placeholder="Enter your {{ $gateway->name }} public key"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('public_key') border-red-500 @enderror">
                                @error('public_key')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Secret Key
                                </label>
                                <input type="password" name="secret_key" 
                                       value="{{ old('secret_key', $gateway->secret_key) }}"
                                       placeholder="Enter your {{ $gateway->name }} secret key"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('secret_key') border-red-500 @enderror">
                                @error('secret_key')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Transaction Fee (%)
                                </label>
                                <input type="number" name="transaction_fee" 
                                       value="{{ old('transaction_fee', $gateway->transaction_fee) }}"
                                       step="0.01" min="0" max="100"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('transaction_fee') border-red-500 @enderror">
                                @error('transaction_fee')
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
                                               @checked(old('is_active', $gateway->is_active))
                                               class="w-4 h-4 text-blue-600">
                                        <span class="ml-2 text-gray-700">Active</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="is_active" value="0" 
                                               @checked(!old('is_active', $gateway->is_active))
                                               class="w-4 h-4 text-gray-600">
                                        <span class="ml-2 text-gray-700">Inactive</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-sm text-blue-800">
                                <strong>Note:</strong> Get your {{ $gateway->name }} API keys from your account settings at 
                                @if($gateway->name === 'Paystack')
                                    <a href="https://dashboard.paystack.com" target="_blank" class="underline">dashboard.paystack.com</a>
                                @elseif($gateway->name === 'Flutterwave')
                                    <a href="https://dashboard.flutterwave.com" target="_blank" class="underline">dashboard.flutterwave.com</a>
                                @endif
                            </p>
                        </div>

                        <div class="flex items-center space-x-4 pt-6 border-t">
                            <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 font-medium">
                                Save {{ $gateway->name }} Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
