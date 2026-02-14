@extends('layouts.app')

@section('title', 'Welcome')

@section('content')
<div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-5xl font-bold mb-6">Welcome to Hostel Manager</h1>
        <p class="text-xl mb-8 text-blue-100">Your complete solution for hostel room booking and management</p>
        
        @if(auth()->check())
            <a href="{{ route('dashboard') }}" class="inline-block bg-white text-blue-600 px-8 py-3 rounded-lg font-bold hover:bg-blue-50">
                Go to Dashboard
            </a>
        @else
            <div class="space-x-4">
                <a href="{{ route('login') }}" class="inline-block bg-white text-blue-600 px-8 py-3 rounded-lg font-bold hover:bg-blue-50">
                    Sign In
                </a>
                <a href="{{ route('register') }}" class="inline-block bg-blue-500 text-white px-8 py-3 rounded-lg font-bold hover:bg-blue-400">
                    Create Account
                </a>
            </div>
        @endif
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-lg transition">
            <h3 class="text-xl font-bold mb-3">For Students</h3>            <div class="text-4xl mb-4">
            <p class="text-gray-600">Browse available rooms, create bookings, and manage your accommodation with ease.</p>
        </div>
        
        <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-lg transition">
            <h3 class="text-xl font-bold mb-3">For Managers</h3>            <div class="text-4xl mb-4">
            <p class="text-gray-600">Manage rooms, approve bookings, and monitor occupancy rates efficiently.</p>
        </div>
        
        <div class="bg-white p-8 rounded-lg shadow-md hover:shadow-lg transition">
            <h3 class="text-xl font-bold mb-3">For Admins</h3>            <div class="text-4xl mb-4">
            <p class="text-gray-600">Oversee multiple hostels, assign managers, and track system-wide statistics.</p>
        </div>
    </div>
</div>
@endsection
