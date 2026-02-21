<x-dashboard-layout :title="__('My ID Card')">
    <x-slot name="sidebar">
        @include('components.student-sidebar')
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">{{ __('My ID Card') }}</h1>
            <p class="mt-2 text-slate-600 dark:text-slate-300">
                {{ __('Your ID card is generated automatically from your active booking details.') }}
            </p>
        </div>

        @if (session('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if (!$booking)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-amber-900">
                <p class="font-semibold">{{ __('ID not available yet') }}</p>
                <p class="mt-1 text-sm">
                    {{ __('You can download your ID only when your booking is active and payment is confirmed.') }}
                </p>
            </div>
        @else
            <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4">
                <div class="flex flex-wrap gap-3 justify-end mb-4">
                    <a href="{{ route('student.id-card.download.svg') }}" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 font-medium">{{ __('Download SVG') }}</a>
                    <a href="{{ route('student.id-card.download.png') }}" class="px-4 py-2 rounded-lg bg-slate-700 text-white hover:bg-slate-800 font-medium">{{ __('Download PNG') }}</a>
                    <a href="{{ route('student.id-card.download.pdf') }}" class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 font-medium">{{ __('Download PDF') }}</a>
                </div>

                <div class="overflow-x-auto">
                    {!! $svg !!}
                </div>
            </div>
        @endif
    </div>
</x-dashboard-layout>
