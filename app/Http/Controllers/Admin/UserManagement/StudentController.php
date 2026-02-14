<?php

namespace App\Http\Controllers\Admin\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserManagement;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $students = User::where('role', 'student')->paginate(15);
        return view('admin.users.students.index', compact('students'));
    }

    public function show(User $student)
    {
        if ($student->role !== 'student') abort(404);
        $management = $student->userManagement;
        return view('admin.users.students.show', compact('student', 'management'));
    }

    public function updateStatus(Request $request, User $student)
    {
        if ($student->role !== 'student') abort(404);

        $validated = $request->validate([
            'status' => 'required|in:active,inactive,suspended',
            'notes' => 'nullable|string',
        ]);

        $management = $student->userManagement ?? new UserManagement(['user_id' => $student->id]);
        $management->update($validated);

        return redirect()->back()->with('success', 'Student status updated');
    }

    public function delete(User $student)
    {
        if ($student->role !== 'student') abort(404);
        
        $name = $student->name;
        $student->delete();

        return redirect()->route('admin.users.students.index')->with('success', "Student {$name} deleted");
    }
}

