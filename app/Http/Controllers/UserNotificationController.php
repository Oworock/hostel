<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $notifications = $user->notifications()->latest()->paginate(20);

        if ($user->isManager()) {
            return view('manager.notifications.index', compact('notifications'));
        }

        if ($user->isStudent()) {
            return view('student.notifications.index', compact('notifications'));
        }

        return redirect()->route('filament.admin.pages.dashboard');
    }

    public function markAsRead(string $notificationId)
    {
        $user = auth()->user();
        $notification = $user->notifications()->where('id', $notificationId)->first();
        if ($notification && !$notification->read_at) {
            $notification->markAsRead();
        }

        return back();
    }

    public function markAllAsRead(Request $request)
    {
        auth()->user()?->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }
}

