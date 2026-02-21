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
use App\Http\Controllers\Student\ReferralDashboardController as StudentReferralDashboardController;
use App\Http\Controllers\Manager\HostelChangeRequestController as ManagerHostelChangeRequestController;
use App\Http\Controllers\Manager\RoomChangeRequestController as ManagerRoomChangeRequestController;
use App\Http\Controllers\Manager\AssetIssueController as ManagerAssetIssueController;
use App\Http\Controllers\Manager\StaffDirectoryController as ManagerStaffDirectoryController;
use App\Http\Controllers\Api\ApiDocumentationController;
use App\Http\Controllers\PublicRoomController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\FileManagerController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\ReferralPartnerController;
use App\Http\Controllers\Student\RoomChangeRequestController as StudentRoomChangeRequestController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\StaffRegistrationController;
use App\Http\Controllers\StaffPayslipController;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Features;

Route::get('/install', [InstallController::class, 'index'])->name('install.index');
Route::get('/install/setup', [InstallController::class, 'setup'])->name('install.setup');
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
Route::middleware('addon.active:referral-system')->group(function () {
    Route::get('/r/{code}', [ReferralController::class, 'capture'])->name('referrals.capture');
});
Route::middleware(['guest', 'addon.active:referral-system'])->group(function () {
    Route::get('/referrals/register', [ReferralController::class, 'create'])->name('referrals.register.create');
    Route::post('/referrals/register', [ReferralController::class, 'store'])->name('referrals.register.store');
    Route::get('/referral/login', [ReferralPartnerController::class, 'showLogin'])->name('referral.login');
    Route::post('/referral/login', [ReferralPartnerController::class, 'login'])->name('referral.login.submit');
});
Route::middleware(['referral.auth', 'addon.active:referral-system'])->group(function () {
    Route::get('/referral/dashboard', [ReferralPartnerController::class, 'dashboard'])->name('referral.dashboard');
    Route::post('/referral/payouts', [ReferralPartnerController::class, 'storePayoutRequest'])->name('referral.payouts.store');
    Route::post('/referral/popup-dismiss', [ReferralPartnerController::class, 'dismissPopup'])->name('referral.popup.dismiss');
    Route::post('/referral/logout', [ReferralPartnerController::class, 'logout'])->name('referral.logout');
});
Route::middleware(['guest', 'addon.active:staff-payroll'])->group(function () {
    Route::get('/staff/register/{token}', [StaffRegistrationController::class, 'create'])->name('staff.register.create');
    Route::post('/staff/register/{token}', [StaffRegistrationController::class, 'store'])->name('staff.register.store');
});
Route::middleware(['addon.active:staff-payroll', 'signed'])->group(function () {
    Route::get('/staff/payslips/{salaryPayment}', [StaffPayslipController::class, 'show'])->name('staff.payslips.show');
    Route::get('/staff/payslips/{salaryPayment}/pdf', [StaffPayslipController::class, 'pdf'])->name('staff.payslips.pdf');
    Route::get('/staff/payslips/{salaryPayment}/image', [StaffPayslipController::class, 'image'])->name('staff.payslips.image');
});
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
    Route::post('/notifications/popup/{announcement}/dismiss', [UserNotificationController::class, 'dismissLoginPopup'])->name('notifications.popup.dismiss');

    Route::get('/settings/profile', fn () => view('settings.profile'))->name('profile.edit');
    Route::get('/settings/password', fn () => view('settings.password'))->name('user-password.edit');
    Route::get('/settings/appearance', fn () => view('settings.appearance'))->name('appearance.edit');
    Route::get('/settings/two-factor', function () {
        abort_unless(Features::canManageTwoFactorAuthentication(), 403);

        return view('settings.two-factor');
    })->middleware('password.confirm')->name('two-factor.show');

    // Admin routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');
        Route::get('/api-docs', [ApiDocumentationController::class, 'index'])->name('api.docs');
        Route::get('/api-docs/openapi.json', [ApiDocumentationController::class, 'openApi'])->name('api.docs.openapi');
        Route::get('/addons/development-guide', function () {
            abort_unless(file_exists(base_path('ADDON_DEVELOPMENT_GUIDE.html')), 404);
            return response()->file(base_path('ADDON_DEVELOPMENT_GUIDE.html'));
        })->name('addons.development-guide');
        Route::get('/files', [FileManagerController::class, 'index'])->name('files.index');
        Route::post('/files', [FileManagerController::class, 'store'])->name('files.store');
        Route::delete('/files/bulk-delete', [FileManagerController::class, 'bulkDestroy'])->name('files.bulk-destroy');
        Route::get('/files/{uploadedFile}', [FileManagerController::class, 'show'])->name('files.show');
        Route::delete('/files/{uploadedFile}', [FileManagerController::class, 'destroy'])->name('files.destroy');
        Route::delete('/files/system-image/delete', [FileManagerController::class, 'destroySystemImage'])->name('files.system-image.destroy');
        Route::get('/backups/download/{file}', [BackupController::class, 'download'])->name('backups.download');
        Route::delete('/backups/{file}', [BackupController::class, 'destroy'])->name('backups.destroy');
        Route::post('/backups/restore-database/{file}', [BackupController::class, 'restoreDatabase'])->name('backups.restore-database');
        Route::post('/backups/restore-files/{file}', [BackupController::class, 'restoreFiles'])->name('backups.restore-files');
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
        Route::get('assets', [ManagerAssetIssueController::class, 'index'])
            ->middleware('addon.active:asset-management')
            ->name('assets.index');
        Route::get('assets/create', [ManagerAssetIssueController::class, 'createAsset'])
            ->middleware('addon.active:asset-management')
            ->name('assets.create');
        Route::post('assets', [ManagerAssetIssueController::class, 'storeAsset'])
            ->middleware('addon.active:asset-management')
            ->name('assets.store');
        Route::post('assets/{asset}/issues', [ManagerAssetIssueController::class, 'store'])
            ->middleware('addon.active:asset-management')
            ->name('assets.issues.store');
        Route::post('assets/{asset}/movements', [ManagerAssetIssueController::class, 'requestMovement'])
            ->middleware('addon.active:asset-management')
            ->name('assets.movements.request');
        Route::post('asset-movements/{movement}/respond', [ManagerAssetIssueController::class, 'respondMovement'])
            ->middleware('addon.active:asset-management')
            ->name('assets.movements.respond');
        Route::get('files', [FileManagerController::class, 'index'])->name('files.index');
        Route::post('files', [FileManagerController::class, 'store'])->name('files.store');
        Route::delete('files/bulk-delete', [FileManagerController::class, 'bulkDestroy'])->name('files.bulk-destroy');
        Route::get('files/{uploadedFile}', [FileManagerController::class, 'show'])->name('files.show');
        Route::delete('files/{uploadedFile}', [FileManagerController::class, 'destroy'])->name('files.destroy');
        Route::get('staff', [ManagerStaffDirectoryController::class, 'index'])
            ->middleware('addon.active:staff-payroll')
            ->name('staff.index');
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
        Route::middleware('addon.active:referral-system')->group(function () {
            Route::get('referrals', [StudentReferralDashboardController::class, 'index'])->name('referrals.index');
            Route::post('referrals/payouts', [StudentReferralDashboardController::class, 'storePayoutRequest'])->name('referrals.payouts.store');
            Route::post('referrals/popup-dismiss', [StudentReferralDashboardController::class, 'dismissPopup'])->name('referrals.popup.dismiss');
        });
        Route::get('id-card', [\App\Http\Controllers\Student\IdCardController::class, 'show'])->name('id-card.show');
        Route::get('id-card/download/svg', [\App\Http\Controllers\Student\IdCardController::class, 'downloadSvg'])->name('id-card.download.svg');
        Route::get('id-card/download/png', [\App\Http\Controllers\Student\IdCardController::class, 'downloadPng'])->name('id-card.download.png');
        Route::get('id-card/download/pdf', [\App\Http\Controllers\Student\IdCardController::class, 'downloadPdf'])->name('id-card.download.pdf');
    });
});

require __DIR__.'/auth.php';
