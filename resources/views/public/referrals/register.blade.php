@extends('layouts.app')

@section('title', 'Referral Registration')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-10 px-4">
    <div class="w-full max-w-md bg-white rounded-xl shadow p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Referral Partner Registration</h1>
        <p class="text-sm text-gray-600 mb-6">Create a referral partner account (non-student) and get a unique student registration link.</p>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 text-green-800 px-3 py-2 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('referral_link'))
            <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 text-blue-800 px-3 py-2 text-sm">
                Share link:
                <span class="font-mono break-all">{{ session('referral_link') }}</span>
            </div>
        @endif

        @php($inviteToken = request('invite', old('invite')))
        <form method="POST" action="{{ route('referrals.register.store', ['invite' => $inviteToken]) }}" class="space-y-4">
            @csrf
            <input type="hidden" name="invite" value="{{ $inviteToken }}">
            <div>
                <label class="block text-sm font-medium text-gray-700">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 @error('name') border-red-500 @enderror" required>
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 @error('email') border-red-500 @enderror" required>
                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 @error('phone') border-red-500 @enderror">
                @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 @error('password') border-red-500 @enderror" required>
                @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input type="password" name="password_confirmation" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2" required>
            </div>

            <button type="submit" class="w-full rounded-lg bg-blue-600 py-2 text-white font-semibold hover:bg-blue-700">Create Referral Account</button>
        </form>
    </div>
</div>
@endsection
