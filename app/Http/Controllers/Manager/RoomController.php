<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Hostel;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = auth()->user()->hostel->rooms()->with('beds')->paginate(15);
        return view('manager.rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('manager.rooms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_number' => 'required|string',
            'type' => 'required|in:single,double,triple,quad',
            'capacity' => 'required|integer|min:1|max:10',
            'price_per_month' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $validated['hostel_id'] = auth()->user()->hostel_id;

        Room::create($validated);
        return redirect()->route('manager.rooms.index')->with('success', 'Room created successfully');
    }

    public function show(Room $room)
    {
        $this->authorize('view', $room);
        $room->load('beds');
        return view('manager.rooms.show', compact('room'));
    }

    public function edit(Room $room)
    {
        $this->authorize('update', $room);
        return view('manager.rooms.edit', compact('room'));
    }

    public function update(Request $request, Room $room)
    {
        $this->authorize('update', $room);

        $validated = $request->validate([
            'room_number' => 'required|string',
            'type' => 'required|in:single,double,triple,quad',
            'capacity' => 'required|integer|min:1|max:10',
            'price_per_month' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_available' => 'boolean',
        ]);

        $room->update($validated);
        return redirect()->route('manager.rooms.show', $room)->with('success', 'Room updated successfully');
    }

    public function destroy(Room $room)
    {
        $this->authorize('delete', $room);
        $room->delete();
        return redirect()->route('manager.rooms.index')->with('success', 'Room deleted successfully');
    }
}
