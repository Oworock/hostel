@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <h2 class="text-center text-3xl font-bold text-gray-900 mb-6">Sign in to your account</h2>
        
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                       required autofocus>
                @error('email')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" name="password" 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror"
                       required>
                @error('password')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <span class="ml-2 text-sm text-gray-700">Remember me</span>
                </label>
                <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:text-blue-500">Forgot password?</a>
            </div>
            
            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 font-medium">
                Sign in
            </button>
        </form>
        
        <p class="mt-6 text-center text-sm text-gray-600">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-500 font-medium">Register here</a>
        </p>
        
        @if(app()->environment('local'))
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-xs text-gray-500 mb-3">Demo Credentials:</p>
                <button type="button" onclick="setCredentials('admin@hostel.com', 'password')" class="block w-full text-left text-xs text-blue-600 hover:bg-blue-50 p-2 rounded mb-1">
                    Admin: admin@hostel.com
                </button>
                <button type="button" onclick="setCredentials('manager@hostel.com', 'password')" class="block w-full text-left text-xs text-blue-600 hover:bg-blue-50 p-2 rounded mb-1">
                    Manager: manager@hostel.com
                </button>
                <button type="button" onclick="setCredentials('student1@email.com', 'password')" class="block w-full text-left text-xs text-blue-600 hover:bg-blue-50 p-2 rounded">
                    Student: student1@email.com
                </button>
            </div>
        @endif
    </div>
</div>

<script>
function setCredentials(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
}
</script>
@endsection
