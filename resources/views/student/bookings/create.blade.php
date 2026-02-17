<x-dashboard-layout title="Book Room">
    <x-slot name="sidebar">
        @include('components.student-sidebar')
    </x-slot>
<div class="uniform-page">
    <h1 class="text-4xl font-bold text-gray-900 dark:text-slate-100 mb-8">Book Room {{ $room->room_number }}</h1>

    @if(!auth()->user()->profile_image)
        <div class="mb-6 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                <strong>Note:</strong> You need to upload a profile picture before completing your booking.
                <a href="{{ route('student.profile.edit') }}" class="text-blue-600 hover:text-blue-800 font-medium">Update your profile</a>
            </p>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="md:col-span-1">
            @php
                $galleryImages = collect();
                if ($room->cover_image) {
                    $galleryImages->push($room->cover_image);
                }
                foreach ($room->images as $img) {
                    if ($img->image_path && !$galleryImages->contains($img->image_path)) {
                        $galleryImages->push($img->image_path);
                    }
                }
                if ($galleryImages->isEmpty() && $room->hostel?->image_path) {
                    $galleryImages->push($room->hostel->image_path);
                }
            @endphp
            @if($galleryImages->count() > 0)
                <div class="uniform-card overflow-hidden mb-6">
                    <div class="relative w-full h-48 bg-gray-200 dark:bg-slate-800">
                        <img src="{{ asset('storage/' . $galleryImages->first()) }}"
                             alt="Room Image"
                             class="w-full h-full object-cover">
                        @if($galleryImages->count() > 1)
                            <div class="absolute bottom-2 right-2 bg-black bg-opacity-60 text-white px-2 py-1 rounded text-xs">
                                {{ $galleryImages->count() }} images
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="uniform-card p-6 sticky top-4">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100 mb-4">Room Details</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs sm:text-sm text-gray-600 dark:text-slate-300">Room Name</p>
                        <p class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-slate-100 break-words">{{ $room->room_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-slate-300">Room Type</p>
                        <p class="text-lg font-medium text-gray-900 dark:text-slate-100">{{ ucfirst($room->type) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-slate-300">Capacity</p>
                        <p class="text-lg font-medium text-gray-900 dark:text-slate-100">{{ $room->capacity }} beds</p>
                    </div>
                    <div class="border-t border-gray-200 dark:border-slate-700 pt-3 mt-3">
                        <p class="text-sm text-gray-600 dark:text-slate-300">Price</p>
                        <p class="text-2xl sm:text-3xl leading-tight font-bold text-blue-600 dark:text-blue-400">{{ getCurrencySymbol() }}{{ number_format($room->price_per_month, 2) }}</p>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-slate-400">Per {{ ucfirst(getBookingPeriodLabel()) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-slate-300">Hostel</p>
                        <p class="text-lg font-medium text-gray-900 dark:text-slate-100">{{ $room->hostel->name }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="md:col-span-2">
            <div class="uniform-card p-8">
                <form method="POST" action="{{ route('student.bookings.store') }}" class="space-y-6">
                    @csrf

                    <input type="hidden" name="room_id" value="{{ $room->id }}">

                    @if($periodType === 'months')
                        <div>
                            <label for="check_in_date" class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Check-in Date *</label>
                            <input type="date" id="check_in_date" name="check_in_date" value="{{ old('check_in_date') }}"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('check_in_date') border-red-500 @enderror"
                                   min="{{ today()->format('Y-m-d') }}" required>
                            @error('check_in_date')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="check_out_date" class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Check-out Date *</label>
                            <input type="date" id="check_out_date" name="check_out_date" value="{{ old('check_out_date') }}"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('check_out_date') border-red-500 @enderror"
                                   required>
                            @error('check_out_date')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        <div>
                            <label for="academic_session_id" class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Academic Session *</label>
                            <select id="academic_session_id" name="academic_session_id"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('academic_session_id') border-red-500 @enderror"
                                    required onchange="updateSemesters()">
                                <option value="">Select Academic Session</option>
                                @foreach($academicSessions as $session)
                                    <option value="{{ $session->id }}" @selected(old('academic_session_id') == $session->id)>
                                        {{ $session->session_name }} ({{ $session->start_year }}/{{ $session->end_year }})
                                    </option>
                                @endforeach
                            </select>
                            @error('academic_session_id')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="semester_id" class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">
                                @if($periodType === 'semesters')
                                    Semester *
                                @else
                                    Period *
                                @endif
                            </label>
                            <select id="semester_id" name="semester_id"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('semester_id') border-red-500 @enderror"
                                    required>
                                <option value="">Select {{ $periodType === 'semesters' ? 'Semester' : 'Period' }}</option>
                                @foreach($semesters as $semester)
                                    <option value="{{ $semester->id }}"
                                            data-session-id="{{ $semester->academic_session_id }}"
                                            @selected(old('semester_id') == $semester->id)>
                                        @if($periodType === 'semesters')
                                            Semester {{ $semester->semester_number }}
                                        @else
                                            {{ $semester->start_date->format('M d, Y') }} - {{ $semester->end_date->format('M d, Y') }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('semester_id')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    @if(!$availableBeds->isEmpty())
                        <div>
                            <label for="bed_id" class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Select Bed (optional)</label>
                            <select id="bed_id" name="bed_id"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Auto-assign best available</option>
                                @foreach($availableBeds as $bed)
                                    <option value="{{ $bed->id }}" @selected(old('bed_id') == $bed->id)>
                                        {{ $bed->bed_number }} @if($bed->name) - {{ $bed->name }} @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <p class="text-sm text-blue-800 dark:text-blue-200">
                            <strong>Price:</strong>
                            <span id="total-amount">{{ getCurrencySymbol() }}{{ number_format($room->price_per_month, 2) }}</span>
                            <span class="text-xs text-blue-700 dark:text-blue-300">(for 1 {{ getBookingPeriodLabel() }})</span>
                        </p>
                    </div>

                    <div class="flex items-center space-x-4 pt-6 border-t border-gray-200 dark:border-slate-700">
                        <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 font-medium @if(!auth()->user()->profile_image) opacity-50 cursor-not-allowed @endif"
                                @if(!auth()->user()->profile_image) disabled @endif>
                            Confirm Booking
                        </button>
                        <a href="{{ route('student.bookings.available') }}" class="text-gray-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-slate-100 font-medium">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    @if($periodType === 'months')
        document.getElementById('check_in_date').addEventListener('change', calculateTotal);
        document.getElementById('check_out_date').addEventListener('change', calculateTotal);

        function calculateTotal() {
            const checkInDate = new Date(document.getElementById('check_in_date').value);
            const checkOutDate = new Date(document.getElementById('check_out_date').value);

            if (checkInDate && checkOutDate && checkOutDate > checkInDate) {
                const days = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));
                const pricePerDay = {{ $room->price_per_month }} / 30;
                const total = (days * pricePerDay).toFixed(2);
                document.getElementById('total-amount').textContent = '{{ getCurrencySymbol() }}' + parseFloat(total).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
        }
    @else
        function updateSemesters() {
            const sessionId = document.getElementById('academic_session_id').value;
            const semesterSelect = document.getElementById('semester_id');
            const options = semesterSelect.querySelectorAll('option');

            options.forEach(opt => {
                if (opt.value === '') {
                    opt.style.display = 'block';
                } else if (opt.getAttribute('data-session-id') === sessionId) {
                    opt.style.display = 'block';
                } else {
                    opt.style.display = 'none';
                }
            });

            semesterSelect.value = '';
        }
    @endif
</script>
</x-dashboard-layout>
