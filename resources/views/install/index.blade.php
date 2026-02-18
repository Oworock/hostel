<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installer - Server Requirements</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-5xl mx-auto px-4">
        <div class="bg-white rounded-lg shadow-md p-8">
            <h1 class="text-3xl font-bold text-gray-900">Installation - Step 1 of 2</h1>
            <p class="text-gray-600 mt-2">Server requirement checks. Resolve all failed checks before continuing.</p>

            @if($errors->any())
                <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
                    <ul class="list-disc ml-5 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-8 space-y-6">
                @foreach($requirements as $group => $checks)
                    <section>
                        <h2 class="text-lg font-semibold text-gray-900 capitalize">{{ $group }}</h2>
                        <div class="mt-3 overflow-hidden rounded-lg border border-gray-200">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 text-gray-700">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Check</th>
                                        <th class="px-4 py-2 text-left">Current</th>
                                        <th class="px-4 py-2 text-left">Required</th>
                                        <th class="px-4 py-2 text-left">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($checks as $check)
                                        <tr class="border-t border-gray-100">
                                            <td class="px-4 py-2">{{ $check['label'] }}</td>
                                            <td class="px-4 py-2">{{ $check['current'] }}</td>
                                            <td class="px-4 py-2">{{ $check['required'] }}</td>
                                            <td class="px-4 py-2">
                                                @if($check['passed'])
                                                    <span class="inline-flex items-center rounded bg-green-100 px-2 py-1 text-xs font-medium text-green-800">PASS</span>
                                                @else
                                                    <span class="inline-flex items-center rounded bg-red-100 px-2 py-1 text-xs font-medium text-red-800">FAIL</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endforeach
            </div>

            <div class="mt-8 flex items-center justify-between">
                <p class="text-sm {{ $allPassed ? 'text-green-700' : 'text-red-700' }}">
                    {{ $allPassed ? 'All checks passed. You can continue to setup.' : 'Some checks failed. Fix them and reload this page.' }}
                </p>

                <a href="{{ route('install.setup') }}"
                   class="px-6 py-2 rounded-lg font-medium {{ $allPassed ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-gray-300 text-gray-600 pointer-events-none' }}"
                   @if(!$allPassed) aria-disabled="true" @endif>
                    Continue to Setup
                </a>
            </div>
        </div>
    </div>
</body>
</html>
