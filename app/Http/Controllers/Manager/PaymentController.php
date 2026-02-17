<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $manager = auth()->user();
        
        $hostels = Hostel::where('manager_id', $manager->id)->pluck('id');
        
        $payments = Payment::whereHas('booking.room', function($query) use ($hostels) {
            $query->whereIn('hostel_id', $hostels);
        })
            ->with('booking.room', 'user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('manager.payments.index', compact('payments'));
    }
}
