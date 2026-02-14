<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
    @php
        $colors = [
            'success' => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'text' => 'text-green-700', 'icon' => '✓'],
            'error' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'text' => 'text-red-700', 'icon' => '✕'],
            'danger' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'text' => 'text-red-700', 'icon' => '!'],
            'warning' => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'text' => 'text-yellow-700', 'icon' => '⚠'],
            'info' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-700', 'icon' => 'ℹ'],
        ];
        $config = $colors[$type] ?? $colors['info'];
    @endphp
    
    <div class="{{ $config['bg'] }} border {{ $config['border'] }} rounded-lg p-4 {{ $config['text'] }}">
        <div class="flex items-start">
            <div class="flex-shrink-0 text-lg font-bold">{{ $config['icon'] }}</div>
            <div class="ml-3">
                <p class="font-medium">{{ $message }}</p>
                @if($type === 'danger' && $errors->any())
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
