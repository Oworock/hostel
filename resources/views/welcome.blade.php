@extends('layouts.app')

@section('title', __('Welcome'))

@section('content')
@php
    $appName = \App\Models\SystemSetting::getSetting('app_name', 'Universal Hostel Manager');

    $heroTitle = \App\Models\SystemSetting::getSetting('global_header_hero_title', __('Welcome to :app', ['app' => $appName]));
    $heroSubtitle = \App\Models\SystemSetting::getSetting('global_header_hero_subtitle', __('Your complete solution for hostel room booking and management'));

    $guestPrimaryText = \App\Models\SystemSetting::getSetting('global_header_primary_button_text', __('Sign In'));
    $guestSecondaryText = \App\Models\SystemSetting::getSetting('global_header_secondary_button_text', __('Create Account'));
    $guestPrimaryUrl = \App\Models\SystemSetting::getSetting('global_header_primary_button_url', route('login'));
    $guestSecondaryUrl = \App\Models\SystemSetting::getSetting('global_header_secondary_button_url', route('register'));
    $authPrimaryText = \App\Models\SystemSetting::getSetting('global_header_authenticated_cta_text', __('Go to Dashboard'));

    $studentTitle = __('For Students');
    $studentDescription = '<p>' . __('Browse available rooms, create bookings, and manage your accommodation with ease.') . '</p>';

    $managerTitle = __('For Managers');
    $managerDescription = '<p>' . __('Manage rooms, approve bookings, and monitor occupancy rates efficiently.') . '</p>';

    $adminTitle = __('For Admins');
    $adminDescription = '<p>' . __('Oversee multiple hostels, assign managers, and track system-wide statistics.') . '</p>';
    $dynamicSections = \App\Models\WelcomeSection::query()
        ->where('is_active', true)
        ->orderBy('display_order')
        ->orderBy('id')
        ->get();

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

@if($dynamicSections->isNotEmpty())
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
        @foreach($dynamicSections as $section)
            <article class="bg-white dark:bg-slate-900 p-5 md:p-6 rounded-xl shadow-md border border-transparent dark:border-slate-800 h-full flex flex-col">
                @if(!empty($section->image_path))
                    <div class="mb-4">
                        <img src="{{ asset('storage/' . $section->image_path) }}" alt="{{ $section->title }}" class="w-full h-48 sm:h-56 object-cover rounded-lg">
                    </div>
                @endif
                <div class="space-y-4 flex-1">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-slate-100">{{ $section->title }}</h3>
                    <div class="text-gray-600 dark:text-slate-300">{!! $section->content !!}</div>
                </div>
            </article>
        @endforeach
        </div>
    </section>
@else
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
@endif
@endsection
