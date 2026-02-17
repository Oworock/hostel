<?php

use App\Http\Controllers\Api\V1\ManagementApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware(['api.key', 'throttle:120,1'])
    ->group(function () {
        Route::get('health', [ManagementApiController::class, 'health']);

        Route::get('hostels', [ManagementApiController::class, 'listHostels']);
        Route::post('hostels', [ManagementApiController::class, 'createHostel']);
        Route::patch('hostels/{hostel}', [ManagementApiController::class, 'updateHostel']);
        Route::delete('hostels/{hostel}', [ManagementApiController::class, 'deleteHostel']);

        Route::get('rooms', [ManagementApiController::class, 'listRooms']);
        Route::post('rooms', [ManagementApiController::class, 'createRoom']);
        Route::patch('rooms/{room}', [ManagementApiController::class, 'updateRoom']);
        Route::delete('rooms/{room}', [ManagementApiController::class, 'deleteRoom']);

        Route::get('students', [ManagementApiController::class, 'listStudents']);
        Route::post('students', [ManagementApiController::class, 'createStudent']);
        Route::patch('students/{student}', [ManagementApiController::class, 'updateStudent']);
        Route::delete('students/{student}', [ManagementApiController::class, 'deleteStudent']);

        Route::get('bookings', [ManagementApiController::class, 'listBookings']);
        Route::post('bookings', [ManagementApiController::class, 'createBooking']);
        Route::patch('bookings/{booking}', [ManagementApiController::class, 'updateBooking']);

        Route::get('payments', [ManagementApiController::class, 'listPayments']);
        Route::post('payments', [ManagementApiController::class, 'createPayment']);
        Route::patch('payments/{payment}', [ManagementApiController::class, 'updatePayment']);

        Route::get('complaints', [ManagementApiController::class, 'listComplaints']);
        Route::patch('complaints/{complaint}', [ManagementApiController::class, 'updateComplaint']);
    });
