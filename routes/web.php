<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Manager\RoomController;
use App\Http\Controllers\Manager\BookingController as ManagerBookingController;
use App\Http\Controllers\Student\BookingController as StudentBookingController;
use App\Http\Controllers\Student\ComplaintController as StudentComplaintController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');
    });

    // Manager routes
    Route::middleware('manager')->prefix('manager')->name('manager.')->group(function () {
        Route::resource('rooms', RoomController::class);
        Route::get('bookings', [ManagerBookingController::class, 'index'])->name('bookings.index');
        Route::get('bookings/{booking}', [ManagerBookingController::class, 'show'])->name('bookings.show');
        Route::patch('bookings/{booking}/approve', [ManagerBookingController::class, 'approve'])->name('bookings.approve');
        Route::patch('bookings/{booking}/reject', [ManagerBookingController::class, 'reject'])->name('bookings.reject');
        Route::delete('bookings/{booking}/cancel', [ManagerBookingController::class, 'cancel'])->name('bookings.cancel');
    });

    // Student routes
    Route::middleware('student')->prefix('student')->name('student.')->group(function () {
        Route::get('bookings', [StudentBookingController::class, 'index'])->name('bookings.index');
        Route::get('bookings/available', [StudentBookingController::class, 'available'])->name('bookings.available');
        Route::get('rooms/{room}/book', [StudentBookingController::class, 'create'])->name('bookings.create');
        Route::post('bookings', [StudentBookingController::class, 'store'])->name('bookings.store');
        Route::get('bookings/{booking}', [StudentBookingController::class, 'show'])->name('bookings.show');
        Route::delete('bookings/{booking}/cancel', [StudentBookingController::class, 'cancel'])->name('bookings.cancel');
        
        Route::get('complaints', [StudentComplaintController::class, 'index'])->name('complaints.index');
        Route::post('complaints', [StudentComplaintController::class, 'store'])->name('complaints.store');
        Route::get('complaints/{complaint}', [StudentComplaintController::class, 'show'])->name('complaints.show');
    });
});

require __DIR__.'/auth.php';
