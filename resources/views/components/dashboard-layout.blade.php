@props(['title' => 'Dashboard'])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - Hostel Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-lg border-r border-gray-200 fixed h-screen overflow-y-auto">
            <div class="p-6">
                <a href="/" class="flex items-center gap-2">
                    @php
                        $logo = \App\Models\SystemSetting::getSetting('app_logo', '');
                    @endphp
                    @if($logo)
                        <img src="{{ asset('storage/' . $logo) }}" alt="Logo" class="h-10 w-auto">
                    @else
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-xs">HMS</span>
                        </div>
                        <span class="text-lg font-bold text-gray-900">Hostel</span>
                    @endif
                </a>
            </div>

            <nav class="px-4 py-6 space-y-2">
                {{ $sidebar ?? '' }}
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="ml-64 flex-1 flex flex-col min-h-screen">
            <!-- Page Content -->
            <main class="flex-1 p-8">
                @if(session('success'))
                    @include('components.alert', ['type' => 'success', 'message' => session('success')])
                @endif

                @if(session('error'))
                    @include('components.alert', ['type' => 'error', 'message' => session('error')])
                @endif

                @if($errors->any())
                    @include('components.alert', ['type' => 'danger', 'message' => 'Please fix the errors below.'])
                @endif

                {{ $slot }}
            </main>

            @include('components.footer')
        </div>
    </div>
</body>
</html>
