<?php

namespace App\Http\Controllers;

use App\Services\OutboundWebhookService;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $notifications = $user->unreadNotifications()->latest()->paginate(20);

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
        if ($notification) {
            if (!$notification->read_at) {
                $notification->markAsRead();
                app(OutboundWebhookService::class)->dispatch('notification.read', [
                    'notification_id' => $notification->id,
                    'user_id' => $user->id,
                    'type' => $notification->type,
                ]);
            }

            // Remove read notifications from the user's inbox immediately.
            $notification->delete();
        }

        return back();
    }

    public function markAllAsRead(Request $request)
    {
        $user = auth()->user();
        if ($user) {
            $user->unreadNotifications()->update(['read_at' => now()]);
            $user->readNotifications()->delete();
        }

        return back();
    }
}
