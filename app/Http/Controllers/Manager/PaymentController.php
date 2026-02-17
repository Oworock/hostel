<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $manager = auth()->user();

        $hostelIds = $manager->managedHostelIds();

        $payments = Payment::whereHas('booking.room', function($query) use ($hostelIds) {
            $query->whereIn('hostel_id', $hostelIds);
        })
            ->with('booking.room', 'user', 'createdByAdmin')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('manager.payments.index', compact('payments'));
    }
}
