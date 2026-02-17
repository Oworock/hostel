<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Hostel Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col">
        @include('components.navbar')
        
        <main class="flex-1">
            @if(session('success'))
                @include('components.alert', ['type' => 'success', 'message' => session('success')])
            @endif
            
            @if(session('error'))
                @include('components.alert', ['type' => 'error', 'message' => session('error')])
            @endif
            
            @if($errors->any())
                @include('components.alert', ['type' => 'danger', 'message' => 'Please fix the errors below.'])
            @endif
            
            @yield('content')
        </main>
        
        @include('components.footer')
    </div>
</body>
</html>
