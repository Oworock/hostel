@extends('layouts.app')

@section('title', 'Hostels')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-4xl font-bold text-gray-900">Hostels</h1>
        <a href="{{ route('admin.hostels.create') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-medium">
            + Add New Hostel
        </a>
    </div>
    
    <div class="grid grid-cols-1 gap-6">
        @forelse($hostels as $hostel)
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $hostel->name }}</h2>
                        <p class="text-gray-600 mb-3">{{ $hostel->description }}</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <p class="text-sm text-gray-600">Owner</p>
                                <p class="font-medium text-gray-900">{{ $hostel->owner->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Capacity</p>
                                <p class="font-medium text-gray-900">{{ $hostel->total_capacity }} beds</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Location</p>
                                <p class="font-medium text-gray-900">{{ $hostel->city }}, {{ $hostel->state }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Status</p>
                                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $hostel->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $hostel->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-2 ml-4">
                        <a href="{{ route('admin.hostels.show', $hostel) }}" class="bg-blue-100 text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-200 font-medium">
                            View
                        </a>
                        <a href="{{ route('admin.hostels.edit', $hostel) }}" class="bg-yellow-100 text-yellow-600 px-4 py-2 rounded-lg hover:bg-yellow-200 font-medium">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('admin.hostels.destroy', $hostel) }}" onsubmit="return confirm('Are you sure?')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-100 text-red-600 px-4 py-2 rounded-lg hover:bg-red-200 font-medium">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <p class="text-gray-600 text-lg mb-4">No hostels found</p>
                <a href="{{ route('admin.hostels.create') }}" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-medium">
                    Create First Hostel
                </a>
            </div>
        @endforelse
    </div>
    
    <!-- Pagination -->
    <div class="mt-8">
        {{ $hostels->links() }}
    </div>
</div>
@endsection
