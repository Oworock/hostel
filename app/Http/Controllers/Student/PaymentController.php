<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $payments = Payment::where('user_id', $user->id)
            ->with('booking')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('student.payments.index', compact('payments'));
    }
}
