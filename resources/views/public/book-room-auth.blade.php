@extends('layouts.app')

@section('title', __('Continue Booking'))

@section('content')
<section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-14 space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-slate-900">{{ __("Continue Your Booking") }}</h1>
        <p class="text-slate-600 mt-1">{{ __('Choose how you want to continue booking Room :room.', ['room' => $room->room_number]) }}</p>
    </div>

    <x-room-listing-card :room="$room" :action-url="null" />

    <div class="rounded-2xl bg-white border border-slate-200 shadow-md p-6">
        <h2 class="text-xl font-semibold text-slate-900 mb-3">{{ __("Are you an existing student?") }}</h2>
        <p class="text-slate-600 mb-5">{{ __('Login if you already have an account, or register if you are a new student.') }}</p>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('login') }}" class="inline-flex justify-center items-center px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700">{{ __("Login to Book") }}</a>
            <a href="{{ route('register') }}" class="inline-flex justify-center items-center px-5 py-2.5 rounded-xl border border-blue-600 text-blue-700 font-semibold hover:bg-blue-50">{{ __("Register as New Student") }}</a>
        </div>
    </div>
</section>
@endsection
