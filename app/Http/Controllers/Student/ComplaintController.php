<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Booking;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $bookings = Booking::where('user_id', $user->id)->with('room')->get();
        $myComplaints = Complaint::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        return view('student.complaints', compact('bookings', 'myComplaints'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'booking_id' => 'nullable|exists:bookings,id',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'open';

        Complaint::create($validated);

        return redirect()->route('student.complaints.index')->with('success', 'Complaint filed successfully');
    }

    public function show(Complaint $complaint)
    {
        $this->authorize('view', $complaint);

        return view('student.complaints.show', compact('complaint'));
    }
}
