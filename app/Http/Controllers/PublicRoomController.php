<?php

namespace App\Http\Controllers;

use App\Models\Hostel;
use App\Models\Room;
use Illuminate\Http\Request;

class PublicRoomController extends Controller
{
    public function index(Request $request)
    {
        $query = Room::query()
            ->where('is_available', true)
            ->with(['hostel', 'images', 'beds']);

        if ($request->filled('hostel_id')) {
            $query->where('hostel_id', $request->integer('hostel_id'));
        }

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('room_number', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%')
                    ->orWhereHas('hostel', fn ($h) => $h->where('name', 'like', '%' . $search . '%')->orWhere('city', 'like', '%' . $search . '%'));
            });
        }

        if ($request->filled('max_price')) {
            $query->where('price_per_month', '<=', (float) $request->input('max_price'));
        }

        $sort = $request->input('sort', 'price_asc');
        match ($sort) {
            'price_desc' => $query->orderByDesc('price_per_month'),
            'recent' => $query->latest(),
            default => $query->orderBy('price_per_month'),
        };

        $rooms = $query->paginate(12)->withQueryString();
        $hostels = Hostel::where('is_active', true)->orderBy('name')->get();

        return view('public.book-rooms', compact('rooms', 'hostels', 'sort'));
    }

    public function book(Room $room)
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->isStudent()) {
                return redirect()->route('student.bookings.create', $room);
            }

            return redirect()->route('dashboard')->with('error', 'Only students can create room bookings.');
        }

        session(['url.intended' => route('student.bookings.create', $room)]);

        return view('public.book-room-auth', compact('room'));
    }
}
