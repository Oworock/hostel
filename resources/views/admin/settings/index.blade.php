@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900">System Settings</h1>
        <p class="text-gray-600 mt-2">Manage all system configurations and integrations</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <!-- General Settings -->
        <a href="{{ route('admin.settings.general') }}" class="bg-white rounded-lg shadow-md hover:shadow-lg transition p-6">
            <div class="text-3xl mb-3">⚙️</div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">General Settings</h3>
            <p class="text-sm text-gray-600">Configure app name, colors, and basics</p>
        </a>

        <!-- Payment Gateways -->
        <a href="{{ route('admin.settings.payment') }}" class="bg-white rounded-lg shadow-md hover:shadow-lg transition p-6">
            <div class="text-3xl mb-3">💳</div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Payment Gateways</h3>
            <p class="text-sm text-gray-600">Paystack & Flutterwave integration</p>
        </a>

        <!-- SMS Providers -->
        <a href="{{ route('admin.settings.sms') }}" class="bg-white rounded-lg shadow-md hover:shadow-lg transition p-6">
            <div class="text-3xl mb-3">📱</div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">SMS Providers</h3>
            <p class="text-sm text-gray-600">Configure SMS service providers</p>
        </a>

        <!-- User Management -->
        <a href="{{ route('admin.users.index') }}" class="bg-white rounded-lg shadow-md hover:shadow-lg transition p-6">
            <div class="text-3xl mb-3">👥</div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">User Management</h3>
            <p class="text-sm text-gray-600">Manage students and managers</p>
        </a>
    </div>

    <!-- Active Gateways Summary -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Active Payment Gateways</h2>
            <div class="space-y-3">
                @forelse($paymentGateways as $gateway)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                        <span class="font-medium text-gray-900">{{ $gateway->name }}</span>
                        <span class="px-3 py-1 rounded-full text-sm font-medium {{ $gateway->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $gateway->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                @empty
                    <p class="text-gray-600">No payment gateways configured</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Active SMS Providers</h2>
            <div class="space-y-3">
                @forelse($smsProviders as $provider)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                        <span class="font-medium text-gray-900">{{ $provider->name }}</span>
                        <span class="px-3 py-1 rounded-full text-sm font-medium {{ $provider->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $provider->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                @empty
                    <p class="text-gray-600">No SMS providers configured</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
