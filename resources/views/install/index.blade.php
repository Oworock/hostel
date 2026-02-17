<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Installation</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white rounded-lg shadow-md p-8">
            <h1 class="text-3xl font-bold text-gray-900">First-Time Installation</h1>
            <p class="text-gray-600 mt-2">Set up your system details, database, and first admin account.</p>

            @if($errors->any())
                <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
                    <ul class="list-disc ml-5 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('install.store') }}" class="mt-8 space-y-8">
                @csrf

                <section class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900">System Information</h2>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">System Name</label>
                        <input type="text" name="app_name" value="{{ old('app_name', 'Hostel Management System') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">System URL</label>
                        <input type="url" name="app_url" value="{{ old('app_url', request()->getSchemeAndHttpHost()) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2" required>
                    </div>
                </section>

                <section class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900">Database Configuration</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Connection</label>
                            <select name="db_connection" class="w-full border border-gray-300 rounded-lg px-4 py-2" required>
                                <option value="sqlite" @selected(old('db_connection', 'sqlite') === 'sqlite')>SQLite</option>
                                <option value="mysql" @selected(old('db_connection') === 'mysql')>MySQL</option>
                                <option value="pgsql" @selected(old('db_connection') === 'pgsql')>PostgreSQL</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Database</label>
                            <input type="text" name="db_database" value="{{ old('db_database', 'database/database.sqlite') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Host</label>
                            <input type="text" name="db_host" value="{{ old('db_host', '127.0.0.1') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Port</label>
                            <input type="text" name="db_port" value="{{ old('db_port', '3306') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Username</label>
                            <input type="text" name="db_username" value="{{ old('db_username') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                            <input type="password" name="db_password" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                        </div>
                    </div>
                </section>

                <section class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900">Admin Account</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Admin Name</label>
                            <input type="text" name="admin_name" value="{{ old('admin_name') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Admin Email</label>
                            <input type="email" name="admin_email" value="{{ old('admin_email') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Admin Phone</label>
                            <input type="text" name="admin_phone" value="{{ old('admin_phone') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                            <input type="password" name="admin_password" class="w-full border border-gray-300 rounded-lg px-4 py-2" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Confirm Password</label>
                            <input type="password" name="admin_password_confirmation" class="w-full border border-gray-300 rounded-lg px-4 py-2" required>
                        </div>
                    </div>
                </section>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-medium">Install System</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
