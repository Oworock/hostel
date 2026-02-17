@extends('layouts.app')

@section('title', 'Book Room')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-4xl font-bold text-gray-900 mb-8">Book Room {{ $room->room_number }}</h1>
    
    @if(!auth()->user()->profile_image)
        <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <p class="text-sm text-yellow-800">
                <strong>⚠️ Note:</strong> You need to upload a profile picture before completing your booking. 
                <a href="{{ route('filament.admin.resources.users.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">Update your profile</a>
            </p>
        </div>
    @endif
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Room Details & Images -->
        <div class="md:col-span-1">
            <!-- Room Images Gallery -->
            @if($room->images->count() > 0)
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="relative w-full h-48 bg-gray-200" x-data="{ currentImage: 0, images: {{ json_encode($room->images->pluck('image_path')) }} }">
                        <template x-for="(image, index) in images" :key="index">
                            <img x-show="index === currentImage" 
                                 :src="'{{ asset('storage/') }}/' + image" 
                                 alt="Room Image" 
                                 class="w-full h-full object-cover">
                        </template>
                        
                        @if($room->images->count() > 1)
                            <div class="absolute inset-0 flex items-center justify-between px-2">
                                <button @click="currentImage = (currentImage - 1 + images.length) % images.length" 
                                        class="bg-black bg-opacity-50 hover:bg-opacity-70 text-white p-2 rounded">
                                    ❮
                                </button>
                                <button @click="currentImage = (currentImage + 1) % images.length" 
                                        class="bg-black bg-opacity-50 hover:bg-opacity-70 text-white p-2 rounded">
                                    ❯
                                </button>
                            </div>
                            <div class="absolute bottom-2 right-2 bg-black bg-opacity-60 text-white px-2 py-1 rounded text-xs">
                                <span x-text="currentImage + 1 + ' / ' + images.length"></span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
            
            <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Room Details</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-600">Room Number</p>
                        <p class="text-lg font-medium text-gray-900">{{ $room->room_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Room Type</p>
                        <p class="text-lg font-medium text-gray-900">{{ ucfirst($room->type) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Capacity</p>
                        <p class="text-lg font-medium text-gray-900">{{ $room->capacity }} beds</p>
                    </div>
                    <div class="border-t pt-3 mt-3">
                        <p class="text-sm text-gray-600">Price Per Month</p>
                        <p class="text-2xl font-bold text-blue-600">{{ getCurrencySymbol() }}{{ number_format($room->price_per_month, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Hostel</p>
                        <p class="text-lg font-medium text-gray-900">{{ $room->hostel->name }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Booking Form -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-8">
                <form method="POST" action="{{ route('student.bookings.store') }}" class="space-y-6">
                    @csrf
                    
                    <input type="hidden" name="room_id" value="{{ $room->id }}">
                    
                    @if($periodType === 'months')
                        <!-- Date-based booking form -->
                        <div>
                            <label for="check_in_date" class="block text-sm font-medium text-gray-700 mb-1">Check-in Date *</label>
                            <input type="date" id="check_in_date" name="check_in_date" value="{{ old('check_in_date') }}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('check_in_date') border-red-500 @enderror"
                                   min="{{ today()->format('Y-m-d') }}" required>
                            @error('check_in_date')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="check_out_date" class="block text-sm font-medium text-gray-700 mb-1">Check-out Date *</label>
                            <input type="date" id="check_out_date" name="check_out_date" value="{{ old('check_out_date') }}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('check_out_date') border-red-500 @enderror"
                                   required>
                            @error('check_out_date')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        <!-- Semester/Session-based booking form -->
                        <div>
                            <label for="academic_session_id" class="block text-sm font-medium text-gray-700 mb-1">Academic Session *</label>
                            <select id="academic_session_id" name="academic_session_id" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('academic_session_id') border-red-500 @enderror"
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
                            <label for="semester_id" class="block text-sm font-medium text-gray-700 mb-1">
                                @if($periodType === 'semesters')
                                    Semester *
                                @else
                                    Period *
                                @endif
                            </label>
                            <select id="semester_id" name="semester_id" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('semester_id') border-red-500 @enderror"
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
                            <label for="bed_id" class="block text-sm font-medium text-gray-700 mb-1">Select Bed (optional)</label>
                            <select id="bed_id" name="bed_id" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Auto-assign best available</option>
                                @foreach($availableBeds as $bed)
                                    <option value="{{ $bed->id }}" @selected(old('bed_id') == $bed->id)>
                                        {{ $bed->bed_number }} @if($bed->name) - {{ $bed->name }} @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-sm text-blue-800">
                            <strong>Price:</strong> 
                            <span id="total-amount">{{ getCurrencySymbol() }}{{ number_format($room->price_per_month, 2) }}</span>
                            @if($periodType === 'months')
                                <span class="text-xs text-blue-700">(for 1 month)</span>
                            @endif
                        </p>
                    </div>
                    
                    <div class="flex items-center space-x-4 pt-6 border-t">
                        <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 font-medium @if(!auth()->user()->profile_image) opacity-50 cursor-not-allowed @endif"
                                @if(!auth()->user()->profile_image) disabled @endif>
                            Confirm Booking
                        </button>
                        <a href="{{ route('student.bookings.available') }}" class="text-gray-600 hover:text-gray-900 font-medium">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

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
@endsection
