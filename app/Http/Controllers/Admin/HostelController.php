<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use Illuminate\Http\Request;

class HostelController extends Controller
{
    public function index()
    {
        $hostels = Hostel::with('owner')->paginate(15);
        return view('admin.hostels.index', compact('hostels'));
    }

    public function create()
    {
        return view('admin.hostels.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:hostels',
            'description' => 'nullable|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'owner_id' => 'required|exists:users,id',
            'price_per_month' => 'required|numeric|min:0',
            'total_capacity' => 'required|integer|min:1',
        ]);

        Hostel::create($validated);
        return redirect()->route('admin.hostels.index')->with('success', 'Hostel created successfully');
    }

    public function show(Hostel $hostel)
    {
        $hostel->load('owner', 'rooms', 'students');
        return view('admin.hostels.show', compact('hostel'));
    }

    public function edit(Hostel $hostel)
    {
        return view('admin.hostels.edit', compact('hostel'));
    }

    public function update(Request $request, Hostel $hostel)
    {
        $validated = $request->validate([
            'name' => 'required|unique:hostels,name,' . $hostel->id,
            'description' => 'nullable|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'price_per_month' => 'required|numeric|min:0',
            'total_capacity' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $hostel->update($validated);
        return redirect()->route('admin.hostels.show', $hostel)->with('success', 'Hostel updated successfully');
    }

    public function destroy(Hostel $hostel)
    {
        $hostel->delete();
        return redirect()->route('admin.hostels.index')->with('success', 'Hostel deleted successfully');
    }
}
