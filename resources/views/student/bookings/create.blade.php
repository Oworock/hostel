@extends('layouts.app')

@section('title', 'Book Room')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-4xl font-bold text-gray-900 mb-8">Book Room {{ $room->room_number }}</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Room Details -->
        <div class="md:col-span-1">
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
                        <p class="text-2xl font-bold text-blue-600">${{ number_format($room->price_per_month, 2) }}</p>
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
                    
                    @if(!$availableBeds->isEmpty())
                        <div>
                            <label for="bed_id" class="block text-sm font-medium text-gray-700 mb-1">Select Bed (optional)</label>
                            <select id="bed_id" name="bed_id" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Auto-assign best available</option>
                                @foreach($availableBeds as $bed)
                                    <option value="{{ $bed->id }}" @selected(old('bed_id') == $bed->id)>
                                        {{ $bed->bed_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-sm text-blue-800">
                            <strong>Estimated Total:</strong> 
                            <span id="total-amount">${{ number_format($room->price_per_month, 2) }}</span> 
                            (for 1 month)
                        </p>
                    </div>
                    
                    <div class="flex items-center space-x-4 pt-6 border-t">
                        <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 font-medium">
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

<script>
document.getElementById('check_in_date').addEventListener('change', calculateTotal);
document.getElementById('check_out_date').addEventListener('change', calculateTotal);

function calculateTotal() {
    const checkInDate = new Date(document.getElementById('check_in_date').value);
    const checkOutDate = new Date(document.getElementById('check_out_date').value);
    
    if (checkInDate && checkOutDate && checkOutDate > checkInDate) {
        const days = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));
        const pricePerDay = {{ $room->price_per_month }} / 30;
        const total = (days * pricePerDay).toFixed(2);
        document.getElementById('total-amount').textContent = '$' + parseFloat(total).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
}
</script>
@endsection
