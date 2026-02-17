<x-dashboard-layout title="Students">
    <x-slot name="sidebar">
        @include('components.manager-sidebar')
    </x-slot>

    <div class="uniform-page">
        <div class="uniform-header">
            <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">Students in Assigned Hostels</h1>
            <p class="text-slate-600 dark:text-slate-300 mt-1">View student occupancy and latest booking status across your hostels.</p>
        </div>

        <div class="uniform-grid-3">
            <div class="uniform-card p-5">
                <p class="text-sm text-slate-600 dark:text-slate-300">Total Students</p>
                <p class="text-3xl font-bold text-slate-900 dark:text-slate-100">{{ $students->total() }}</p>
            </div>
            <div class="uniform-card p-5">
                <p class="text-sm text-slate-600 dark:text-slate-300">Active Stay</p>
                <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $students->filter(fn($student) => $student->bookings->firstWhere('status', 'approved'))->count() }}</p>
            </div>
            <div class="uniform-card p-5">
                <p class="text-sm text-slate-600 dark:text-slate-300">Pending/Other</p>
                <p class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ max(0, $students->count() - $students->filter(fn($student) => $student->bookings->firstWhere('status', 'approved'))->count()) }}</p>
            </div>
        </div>

        <div class="uniform-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1080px]">
                    <thead class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Student</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Contact</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Additional Info</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Current Room</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Check-in</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Booking Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($students as $student)
                            @php
                                $activeBooking = $student->bookings->firstWhere('status', 'approved');
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">
                                    <div class="flex items-center gap-3">
                                        @if($student->profile_image)
                                            <img src="{{ asset('storage/' . $student->profile_image) }}" alt="{{ $student->name }}" class="w-10 h-10 rounded-full object-cover border border-slate-200 dark:border-slate-700">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-700 dark:text-slate-200 font-semibold">
                                                {{ strtoupper(substr($student->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-semibold">{{ $student->name }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $student->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $student->phone ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                                    @php
                                        $extraData = is_array($student->extra_data ?? null) ? collect($student->extra_data)->filter() : collect();
                                    @endphp
                                    @if($extraData->isNotEmpty())
                                        <div class="space-y-1">
                                            @foreach($extraData->take(2) as $key => $value)
                                                <p><span class="font-medium text-slate-800 dark:text-slate-100">{{ ucwords(str_replace('_', ' ', $key)) }}:</span> {{ $value }}</p>
                                            @endforeach
                                            @if($extraData->count() > 2)
                                                <p class="text-xs text-slate-500 dark:text-slate-400">+{{ $extraData->count() - 2 }} more</p>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">
                                    @if($activeBooking)
                                        Room {{ $activeBooking->room->room_number }}
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500">No active booking</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                                    @if($activeBooking)
                                        {{ $activeBooking->check_in_date->format('M d, Y') }}
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if($activeBooking)
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">Active</span>
                                    @elseif($student->bookings->count() > 0)
                                        @php
                                            $latestBooking = $student->bookings->first();
                                        @endphp
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                                            @if($latestBooking->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300
                                            @elseif($latestBooking->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                                            @else bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300
                                            @endif">
                                            {{ ucfirst($latestBooking->status) }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500">No bookings</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-600 dark:text-slate-300">No students found in your assigned hostels.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $students->links() }}
        </div>
    </div>
</x-dashboard-layout>
