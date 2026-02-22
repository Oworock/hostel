@extends('layouts.app')

@section('title', 'Referral Partner Login')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md bg-white rounded-xl shadow p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Referral Partner Login</h1>
        <p class="text-sm text-gray-600 mb-6">Login to your referral dashboard.</p>

        <form method="POST" action="{{ route('referral.login.submit') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 @error('email') border-red-500 @enderror" required>
                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 @error('password') border-red-500 @enderror" required>
                @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="w-full rounded-lg bg-blue-600 text-white py-2 font-semibold hover:bg-blue-700">Login</button>
        </form>
    </div>
</div>
@endsection

