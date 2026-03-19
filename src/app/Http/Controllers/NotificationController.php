<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(Notification $notification)
    {
        abort_unless($notification->user_id === Auth::id(), 403);

        $notification->update(['read_at' => now()]);

        return back()->with('success', __('messages.notifications.marked_read'));
    }

    public function markAllRead()
    {
        Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', __('messages.notifications.all_marked_read'));
    }

    public function destroy(Notification $notification)
    {
        abort_unless($notification->user_id === Auth::id(), 403);

        $notification->delete();

        return back()->with('success', __('messages.notifications.deleted'));
    }

    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'preferences' => 'required|array',
            'preferences.new_event' => 'in:push,email,both,none',
            'preferences.event_reminder' => 'in:push,none',
            'preferences.wall_posts' => 'in:push,none',
            'preferences.comments' => 'in:push,none',
            'preferences.silent_from' => 'nullable|date_format:H:i',
            'preferences.silent_to' => 'nullable|date_format:H:i',
        ]);

        Auth::user()->update([
            'notification_preferences' => $validated['preferences'],
        ]);

        return back()->with('success', __('messages.notifications.preferences_saved'));
    }
}
