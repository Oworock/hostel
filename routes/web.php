<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Manager\RoomController;
use App\Http\Controllers\Manager\BookingController as ManagerBookingController;
use App\Http\Controllers\Manager\PaymentController as ManagerPaymentController;
use App\Http\Controllers\Manager\ProfileController as ManagerProfileController;
use App\Http\Controllers\Manager\ComplaintController as ManagerComplaintController;
use App\Http\Controllers\Student\BookingController as StudentBookingController;
use App\Http\Controllers\Student\ComplaintController as StudentComplaintController;
use App\Http\Controllers\Student\HostelChangeRequestController as StudentHostelChangeRequestController;
use App\Http\Controllers\Student\PaymentController as StudentPaymentController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController;
use App\Http\Controllers\Manager\HostelChangeRequestController as ManagerHostelChangeRequestController;
use App\Http\Controllers\Manager\RoomChangeRequestController as ManagerRoomChangeRequestController;
use App\Http\Controllers\Api\ApiDocumentationController;
use App\Http\Controllers\PublicRoomController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\Student\RoomChangeRequestController as StudentRoomChangeRequestController;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/install', [InstallController::class, 'index'])->name('install.index');
Route::post('/install', [InstallController::class, 'store'])->name('install.store');

Route::get('/', function () {
    $homepageEnabled = filter_var(SystemSetting::getSetting('homepage_enabled', true), FILTER_VALIDATE_BOOL);
    if (!$homepageEnabled) {
        return redirect()->route('login');
    }

    return view('welcome');
});
Route::get('/book-rooms', [PublicRoomController::class, 'index'])->name('public.rooms.index');
Route::get('/book-rooms/{room}/book', [PublicRoomController::class, 'book'])->name('public.rooms.book');
Route::get('student/payments/callback/paystack', [StudentPaymentController::class, 'paystackCallback'])->name('student.payments.callback.paystack');
Route::get('student/payments/callback/flutterwave', [StudentPaymentController::class, 'flutterwaveCallback'])->name('student.payments.callback.flutterwave');
Route::get('student/payments/callback/stripe', [StudentPaymentController::class, 'stripeCallback'])->name('student.payments.callback.stripe');
Route::get('student/payments/callback/paypal', [StudentPaymentController::class, 'paypalCallback'])->name('student.payments.callback.paypal');
Route::get('student/payments/callback/razorpay', [StudentPaymentController::class, 'razorpayCallback'])->name('student.payments.callback.razorpay');
Route::get('student/payments/callback/square', [StudentPaymentController::class, 'squareCallback'])->name('student.payments.callback.square');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/impersonation/leave', function () {
        $impersonatorId = session('impersonator_id');
        abort_unless($impersonatorId, 403);

        Auth::loginUsingId($impersonatorId);
        session()->forget('impersonator_id');

        return redirect()->route('filament.admin.pages.dashboard');
    })->name('impersonation.leave');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/notifications', [UserNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [UserNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/{notificationId}/read', [UserNotificationController::class, 'markAsRead'])->name('notifications.read');

    // Admin routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');
        Route::get('/api-docs', [ApiDocumentationController::class, 'index'])->name('api.docs');
        Route::get('/api-docs/openapi.json', [ApiDocumentationController::class, 'openApi'])->name('api.docs.openapi');
    });

    // Manager routes
    Route::middleware('manager')->prefix('manager')->name('manager.')->group(function () {
        Route::get('students', [ManagerBookingController::class, 'students'])->name('students.index');
        Route::resource('rooms', RoomController::class);
        Route::post('rooms/{room}/beds', [RoomController::class, 'addBed'])->name('rooms.beds.store');
        Route::get('bookings', [ManagerBookingController::class, 'index'])->name('bookings.index');
        Route::get('bookings/{booking}', [ManagerBookingController::class, 'show'])->name('bookings.show');
        Route::patch('bookings/{booking}/approve', [ManagerBookingController::class, 'approve'])->name('bookings.approve');
        Route::patch('bookings/{booking}/reject', [ManagerBookingController::class, 'reject'])->name('bookings.reject');
        Route::delete('bookings/{booking}/cancel', [ManagerBookingController::class, 'cancel'])->name('bookings.cancel');
        Route::get('complaints', [ManagerComplaintController::class, 'index'])->name('complaints.index');
        Route::patch('complaints/{complaint}/respond', [ManagerComplaintController::class, 'respond'])->name('complaints.respond');
        Route::get('payments', [ManagerPaymentController::class, 'index'])->name('payments.index');
        Route::get('hostel-change-requests', [ManagerHostelChangeRequestController::class, 'index'])->name('hostel-change.index');
        Route::patch('hostel-change-requests/{hostelChangeRequest}/approve', [ManagerHostelChangeRequestController::class, 'approve'])->name('hostel-change.approve');
        Route::patch('hostel-change-requests/{hostelChangeRequest}/reject', [ManagerHostelChangeRequestController::class, 'reject'])->name('hostel-change.reject');
        Route::get('room-change-requests', [ManagerRoomChangeRequestController::class, 'index'])->name('room-change.index');
        Route::patch('room-change-requests/{roomChangeRequest}/approve', [ManagerRoomChangeRequestController::class, 'approve'])->name('room-change.approve');
        Route::patch('room-change-requests/{roomChangeRequest}/reject', [ManagerRoomChangeRequestController::class, 'reject'])->name('room-change.reject');
        Route::get('profile', [ManagerProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ManagerProfileController::class, 'update'])->name('profile.update');
    });

    // Student routes
    Route::middleware('student')->prefix('student')->name('student.')->group(function () {
        Route::get('bookings', [StudentBookingController::class, 'index'])->name('bookings.index');
        Route::get('bookings/available', [StudentBookingController::class, 'available'])->name('bookings.available');
        Route::get('rooms/{room}/book', [StudentBookingController::class, 'create'])->name('bookings.create');
        Route::post('bookings', [StudentBookingController::class, 'store'])->name('bookings.store');
        Route::get('bookings/{booking}', [StudentBookingController::class, 'show'])->name('bookings.show');
        Route::delete('bookings/{booking}/cancel', [StudentBookingController::class, 'cancel'])->name('bookings.cancel');
        Route::get('bookings/{booking}/receipt', [StudentBookingController::class, 'receipt'])->name('bookings.receipt');
        
        Route::get('complaints', [StudentComplaintController::class, 'index'])->name('complaints.index');
        Route::post('complaints', [StudentComplaintController::class, 'store'])->name('complaints.store');
        Route::get('complaints/{complaint}', [StudentComplaintController::class, 'show'])->name('complaints.show');
        
        Route::get('payments', [StudentPaymentController::class, 'index'])->name('payments.index');
        Route::post('payments/{booking}/{gateway}/initialize', [StudentPaymentController::class, 'initialize'])->name('payments.initialize');
        Route::get('hostel-change-requests', [StudentHostelChangeRequestController::class, 'index'])->name('hostel-change.index');
        Route::post('hostel-change-requests', [StudentHostelChangeRequestController::class, 'store'])->name('hostel-change.store');
        Route::get('room-change-requests', [StudentRoomChangeRequestController::class, 'index'])->name('room-change.index');
        Route::post('room-change-requests', [StudentRoomChangeRequestController::class, 'store'])->name('room-change.store');
        Route::get('profile', [StudentProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [StudentProfileController::class, 'update'])->name('profile.update');
    });
});

require __DIR__.'/auth.php';
