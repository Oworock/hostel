@extends('layouts.app')

@section('title', $hostel->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-900">{{ $hostel->name }}</h1>
            <p class="text-gray-600 mt-2">{{ $hostel->address }}, {{ $hostel->city }}, {{ $hostel->state }}</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.hostels.edit', $hostel) }}" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700">
                Edit
            </a>
            <form method="POST" action="{{ route('admin.hostels.destroy', $hostel) }}" onsubmit="return confirm('Are you sure?')" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                    Delete
                </button>
            </form>
        </div>
    </div>
    
    <!-- Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-600 text-sm">Owner</p>
            <p class="text-lg font-bold text-gray-900">{{ $hostel->owner->name }}</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-600 text-sm">Total Capacity</p>
            <p class="text-lg font-bold text-gray-900">{{ $hostel->total_capacity }} beds</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-600 text-sm">Price Per {{ ucfirst(getBookingPeriodLabel()) }}</p>
            <p class="text-lg font-bold text-gray-900">{{ formatCurrency($hostel->price_per_month) }}</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-600 text-sm">Total Rooms</p>
            <p class="text-lg font-bold text-gray-900">{{ $hostel->rooms()->count() }}</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-600 text-sm">Total Students</p>
            <p class="text-lg font-bold text-gray-900">{{ $hostel->students()->count() }}</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-600 text-sm">Status</p>
            <span class="px-3 py-1 rounded-full text-sm font-medium {{ $hostel->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ $hostel->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
    </div>
    
    <!-- Description -->
    @if($hostel->description)
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Description</h2>
            <p class="text-gray-600">{{ $hostel->description }}</p>
        </div>
    @endif
    
    <!-- Managers -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Managers</h2>
        <div class="space-y-3">
            @forelse($hostel->managers() as $manager)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                    <div>
                        <p class="font-medium text-gray-900">{{ $manager->name }}</p>
                        <p class="text-sm text-gray-600">{{ $manager->email }}</p>
                    </div>
                    <span class="text-sm text-gray-600">{{ $manager->phone }}</span>
                </div>
            @empty
                <p class="text-gray-600">No managers assigned</p>
            @endforelse
        </div>
    </div>
    
    <!-- Contact Information -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Contact Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @if($hostel->phone)
                <div>
                    <p class="text-gray-600 text-sm">Phone</p>
                    <p class="text-lg font-medium text-gray-900">{{ $hostel->phone }}</p>
                </div>
            @endif
            @if($hostel->email)
                <div>
                    <p class="text-gray-600 text-sm">Email</p>
                    <p class="text-lg font-medium text-gray-900">{{ $hostel->email }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
