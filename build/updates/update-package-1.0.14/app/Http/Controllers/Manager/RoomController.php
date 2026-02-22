<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Bed;
use App\Models\Hostel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    public function index()
    {
        $hostelIds = auth()->user()->managedHostelIds();
        $rooms = Room::whereIn('hostel_id', $hostelIds)->with(['beds', 'hostel'])->paginate(15);
        return view('manager.rooms.index', compact('rooms'));
    }

    public function create()
    {
        $hostels = auth()->user()->managedHostels()->orderBy('name')->get();
        if ($hostels->isEmpty() && auth()->user()->hostel_id) {
            $hostels = Hostel::whereKey(auth()->user()->hostel_id)->get();
        }
        return view('manager.rooms.create', compact('hostels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'hostel_id' => 'required|exists:hostels,id',
            'room_number' => 'required|string',
            'type' => 'required|in:single,double,triple,quad,quint,sext,sept,oct',
            'capacity' => 'required|integer|min:1|max:8',
            'price_per_month' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|max:5120',
        ]);
        if (!auth()->user()->managedHostelIds()->contains((int) $validated['hostel_id'])) {
            abort(403, 'Unauthorized hostel assignment');
        }
        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('rooms', 'public');
        }

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
        $hostels = auth()->user()->managedHostels()->orderBy('name')->get();
        if ($hostels->isEmpty() && auth()->user()->hostel_id) {
            $hostels = Hostel::whereKey(auth()->user()->hostel_id)->get();
        }
        return view('manager.rooms.edit', compact('room', 'hostels'));
    }

    public function update(Request $request, Room $room)
    {
        $this->authorize('update', $room);

        $validated = $request->validate([
            'hostel_id' => 'required|exists:hostels,id',
            'room_number' => 'required|string',
            'type' => 'required|in:single,double,triple,quad,quint,sext,sept,oct',
            'capacity' => 'required|integer|min:1|max:8',
            'price_per_month' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_available' => 'boolean',
            'cover_image' => 'nullable|image|max:5120',
            'remove_cover_image' => 'nullable|boolean',
        ]);
        if (!auth()->user()->managedHostelIds()->contains((int) $validated['hostel_id'])) {
            abort(403, 'Unauthorized hostel assignment');
        }

        if ($request->boolean('remove_cover_image') && !empty($room->cover_image)) {
            Storage::disk('public')->delete($room->cover_image);
            $validated['cover_image'] = null;
        }

        if ($request->hasFile('cover_image')) {
            if (!empty($room->cover_image)) {
                Storage::disk('public')->delete($room->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('rooms', 'public');
        }

        $room->update($validated);
        return redirect()->route('manager.rooms.show', $room)->with('success', 'Room updated successfully');
    }

    public function destroy(Room $room)
    {
        $this->authorize('delete', $room);
        $room->delete();
        return redirect()->route('manager.rooms.index')->with('success', 'Room deleted successfully');
    }

    public function addBed(Request $request, Room $room)
    {
        $this->authorize('update', $room);

        $validated = $request->validate([
            'bed_number' => ['required', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        Bed::create([
            'room_id' => $room->id,
            'bed_number' => $validated['bed_number'],
            'name' => $validated['name'] ?? null,
            'is_occupied' => false,
            'is_approved' => false,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('manager.rooms.show', $room)->with('success', 'Bed space added and awaiting admin approval.');
    }
}
