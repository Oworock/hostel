<?php

namespace App\Http\Controllers\Admin\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Hostel;
use App\Models\UserManagement;
use Illuminate\Http\Request;

class ManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $managers = User::where('role', 'manager')->paginate(15);
        return view('admin.users.managers.index', compact('managers'));
    }

    public function show(User $manager)
    {
        if ($manager->role !== 'manager') abort(404);
        $hostels = Hostel::where('owner_id', $manager->id)->get();
        $management = $manager->userManagement;
        return view('admin.users.managers.show', compact('manager', 'hostels', 'management'));
    }

    public function assignHostel(Request $request, User $manager)
    {
        if ($manager->role !== 'manager') abort(404);

        $validated = $request->validate([
            'hostel_id' => 'required|exists:hostels,id',
        ]);

        Hostel::find($validated['hostel_id'])->update(['owner_id' => $manager->id]);

        return redirect()->back()->with('success', 'Hostel assigned to manager');
    }

    public function updateStatus(Request $request, User $manager)
    {
        if ($manager->role !== 'manager') abort(404);

        $validated = $request->validate([
            'status' => 'required|in:active,inactive,suspended',
            'notes' => 'nullable|string',
        ]);

        $management = $manager->userManagement ?? new UserManagement(['user_id' => $manager->id]);
        $management->update($validated);

        return redirect()->back()->with('success', 'Manager status updated');
    }

    public function delete(User $manager)
    {
        if ($manager->role !== 'manager') abort(404);
        
        $name = $manager->name;
        $manager->delete();

        return redirect()->route('admin.users.managers.index')->with('success', "Manager {$name} deleted");
    }
}

