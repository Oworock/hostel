@extends('layouts.app')

@section('title', 'Welcome')

@section('content')
@php
    $appName = \App\Models\SystemSetting::getSetting('app_name', 'Hostel Manager');

    $heroTitle = \App\Models\SystemSetting::getSetting('global_header_hero_title', 'Welcome to ' . $appName);
    $heroSubtitle = \App\Models\SystemSetting::getSetting('global_header_hero_subtitle', 'Your complete solution for hostel room booking and management');

    $guestPrimaryText = \App\Models\SystemSetting::getSetting('global_header_primary_button_text', 'Sign In');
    $guestSecondaryText = \App\Models\SystemSetting::getSetting('global_header_secondary_button_text', 'Create Account');
    $guestPrimaryUrl = \App\Models\SystemSetting::getSetting('global_header_primary_button_url', route('login'));
    $guestSecondaryUrl = \App\Models\SystemSetting::getSetting('global_header_secondary_button_url', route('register'));
    $authPrimaryText = \App\Models\SystemSetting::getSetting('global_header_authenticated_cta_text', 'Go to Dashboard');

    $studentTitle = \App\Models\SystemSetting::getSetting('welcome_body_student_title', 'For Students');
    $studentDescription = \App\Models\SystemSetting::getSetting('welcome_body_student_description', 'Browse available rooms, create bookings, and manage your accommodation with ease.');

    $managerTitle = \App\Models\SystemSetting::getSetting('welcome_body_manager_title', 'For Managers');
    $managerDescription = \App\Models\SystemSetting::getSetting('welcome_body_manager_description', 'Manage rooms, approve bookings, and monitor occupancy rates efficiently.');

    $adminTitle = \App\Models\SystemSetting::getSetting('welcome_body_admin_title', 'For Admins');
    $adminDescription = \App\Models\SystemSetting::getSetting('welcome_body_admin_description', 'Oversee multiple hostels, assign managers, and track system-wide statistics.');

    $toUrl = function (string $value): string {
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return url($value);
    };
@endphp

<section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16 md:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl md:text-5xl font-bold mb-4 md:mb-6">{{ $heroTitle }}</h1>
        <p class="text-base md:text-xl mb-8 text-blue-100 max-w-3xl mx-auto">{{ $heroSubtitle }}</p>

        @if(auth()->check())
            <a href="{{ route('dashboard') }}" class="inline-block bg-white text-blue-600 px-6 md:px-8 py-3 rounded-lg font-bold hover:bg-blue-50">
                {{ $authPrimaryText }}
            </a>
        @else
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ $toUrl($guestPrimaryUrl) }}" class="w-full sm:w-auto inline-block bg-white text-blue-600 px-6 md:px-8 py-3 rounded-lg font-bold hover:bg-blue-50">
                    {{ $guestPrimaryText }}
                </a>
                <a href="{{ $toUrl($guestSecondaryUrl) }}" class="w-full sm:w-auto inline-block bg-blue-500 text-white px-6 md:px-8 py-3 rounded-lg font-bold hover:bg-blue-400">
                    {{ $guestSecondaryText }}
                </a>
            </div>
        @endif
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
        <article class="bg-white dark:bg-slate-900 p-6 md:p-8 rounded-lg shadow-md hover:shadow-lg transition border border-transparent dark:border-slate-800">
            <h3 class="text-xl font-bold mb-3 text-gray-900 dark:text-slate-100">{{ $studentTitle }}</h3>
            <div class="text-gray-600 dark:text-slate-300">{!! $studentDescription !!}</div>
        </article>

        <article class="bg-white dark:bg-slate-900 p-6 md:p-8 rounded-lg shadow-md hover:shadow-lg transition border border-transparent dark:border-slate-800">
            <h3 class="text-xl font-bold mb-3 text-gray-900 dark:text-slate-100">{{ $managerTitle }}</h3>
            <div class="text-gray-600 dark:text-slate-300">{!! $managerDescription !!}</div>
        </article>

        <article class="bg-white dark:bg-slate-900 p-6 md:p-8 rounded-lg shadow-md hover:shadow-lg transition border border-transparent dark:border-slate-800">
            <h3 class="text-xl font-bold mb-3 text-gray-900 dark:text-slate-100">{{ $adminTitle }}</h3>
            <div class="text-gray-600 dark:text-slate-300">{!! $adminDescription !!}</div>
        </article>
    </div>
</section>
@endsection
