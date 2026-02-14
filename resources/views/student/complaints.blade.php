@extends('layouts.app')

@section('title', 'File Complaint')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">File a Complaint</h1>
    
    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    <form action="{{ route('student.complaints.store') }}" method="POST" class="bg-white rounded-lg shadow-md p-6">
        @csrf

        <div class="space-y-6">
            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                <input 
                    type="text" 
                    id="subject" 
                    name="subject" 
                    value="{{ old('subject') }}"
                    placeholder="Brief description of your complaint"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    required
                >
                @error('subject')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea 
                    id="description" 
                    name="description"
                    rows="6"
                    placeholder="Please provide detailed information about your complaint..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    required
                ></textarea>
                @error('description')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="booking_id" class="block text-sm font-medium text-gray-700 mb-1">Related Booking (Optional)</label>
                <select 
                    id="booking_id" 
                    name="booking_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="">Select a booking...</option>
                    @forelse($bookings as $booking)
                        <option value="{{ $booking->id }}">
                            {{ $booking->room->name ?? 'Room' }} - {{ $booking->check_in_date->format('M d, Y') }}
                        </option>
                    @empty
                        <option disabled>No bookings found</option>
                    @endforelse
                </select>
                @error('booking_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-4">
                <button 
                    type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-medium"
                >
                    Submit Complaint
                </button>
                <a 
                    href="{{ route('student.dashboard') }}"
                    class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium"
                >
                    Cancel
                </a>
            </div>
        </div>
    </form>

    <!-- My Complaints Section -->
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">My Complaints</h2>
        
        @forelse($myComplaints as $complaint)
            <div class="bg-white rounded-lg shadow-md p-6 mb-4">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $complaint->subject }}</h3>
                        <p class="text-gray-600 mt-1">{{ Str::limit($complaint->description, 200) }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm font-medium @if($complaint->status === 'resolved') bg-green-100 text-green-800 @elseif($complaint->status === 'in_progress') bg-blue-100 text-blue-800 @elseif($complaint->status === 'closed') bg-gray-100 text-gray-800 @else bg-yellow-100 text-yellow-800 @endif">
                        {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
                    </span>
                </div>

                @if($complaint->response)
                    <div class="mt-4 p-4 bg-blue-50 border-l-4 border-blue-500">
                        <p class="text-sm font-medium text-blue-900">Manager Response:</p>
                        <p class="text-sm text-blue-800 mt-1">{{ $complaint->response }}</p>
                    </div>
                @endif

                <div class="mt-4 text-xs text-gray-500">
                    Filed on {{ $complaint->created_at->format('M d, Y H:i') }}
                    @if($complaint->assigned_to)
                        | Assigned to: {{ $complaint->assignedManager->name ?? 'Unknown' }}
                    @endif
                </div>
            </div>
        @empty
            <p class="text-gray-600 text-center py-8">No complaints filed yet</p>
        @endforelse
    </div>
</div>
@endsection
