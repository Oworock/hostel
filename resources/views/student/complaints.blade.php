<x-dashboard-layout :title="__('Complaints')">
    <x-slot name="sidebar">
        @include('components.student-sidebar')
    </x-slot>

    <div class="uniform-page">
        <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">{{ __("File a Complaint") }}</h1>

        @if ($errors->any())
            <div class="p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">{{ __('Please fix the following errors:') }}</h3>
                <ul class="mt-2 text-sm text-red-700 dark:text-red-300 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</p>
            </div>
        @endif

        <div class="uniform-grid-2">
            <form action="{{ route('student.complaints.store') }}" method="POST" class="uniform-card p-6 space-y-6">
                @csrf

                <div>
                    <label for="subject" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">{{ __("Subject") }}</label>
                    <input type="text" id="subject" name="subject" value="{{ old('subject') }}" placeholder="{{ __("Brief description of your complaint") }}" class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    @error('subject')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">{{ __("Description") }}</label>
                    <textarea id="description" name="description" rows="6" placeholder="{{ __("Please provide detailed information about your complaint...") }}" class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>{{ old('description') }}</textarea>
                    @error('description')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="booking_id" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">{{ __("Related Booking (Optional)") }}</label>
                    <select id="booking_id" name="booking_id" class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select a booking...</option>
                        @forelse($bookings as $booking)
                            <option value="{{ $booking->id }}">{{ $booking->room->room_number ?? 'Room' }} - {{ $booking->check_in_date->format('M d, Y') }}</option>
                        @empty
                            <option disabled>{{ __('No bookings found') }}</option>
                        @endforelse
                    </select>
                    @error('booking_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 font-medium">{{ __("Submit Complaint") }}</button>
                    <a href="{{ route('dashboard') }}" class="px-6 py-2.5 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 font-medium">Cancel</a>
                </div>
            </form>

            <div class="space-y-4">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ __("My Complaints") }}</h2>
            @forelse($myComplaints as $complaint)
                <article class="uniform-card p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $complaint->subject }}</h3>
                            <p class="text-slate-600 dark:text-slate-300 mt-1">{{ Str::limit($complaint->description, 200) }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold @if($complaint->status === 'resolved') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 @elseif($complaint->status === 'in_progress') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 @elseif($complaint->status === 'closed') bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300 @endif">
                            {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
                        </span>
                    </div>

                    @if($complaint->response)
                        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/30 border-l-4 border-blue-500 rounded-r-lg">
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-200">Manager Response:</p>
                            <p class="text-sm text-blue-800 dark:text-blue-300 mt-1">{{ $complaint->response }}</p>
                        </div>
                    @endif

                    <div class="mt-4 text-xs text-slate-500 dark:text-slate-400">
                        Filed on {{ $complaint->created_at->format('M d, Y H:i') }}
                        @if($complaint->assigned_to)
                            | Assigned to: {{ $complaint->assignedManager->name ?? 'Unknown' }}
                        @endif
                    </div>
                </article>
            @empty
                <p class="text-slate-600 dark:text-slate-300 text-center py-8">{{ __('No complaints filed yet.') }}</p>
            @endforelse
            </div>
        </div>
    </div>
</x-dashboard-layout>
