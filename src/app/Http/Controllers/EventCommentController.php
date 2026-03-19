<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventComment;
use App\Models\TeamMembership;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventCommentController extends Controller
{
    public function store(Request $request, Event $event)
    {
        $clubId = session('current_club_id');
        abort_unless($event->club_id === $clubId, 403);

        $validated = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        EventComment::create([
            'event_id' => $event->id,
            'user_id' => Auth::id(),
            'body' => $validated['body'],
        ]);

        // Notify other commenters (watchers) + team coaches, excluding the author
        $watcherIds = EventComment::where('event_id', $event->id)
            ->where('user_id', '!=', Auth::id())
            ->distinct()
            ->pluck('user_id');

        $coachIds = TeamMembership::where('team_id', $event->team_id)
            ->whereIn('role', ['head_coach', 'assistant_coach'])
            ->where('status', 'active')
            ->where('user_id', '!=', Auth::id())
            ->pluck('user_id');

        $notifyIds = $watcherIds->merge($coachIds)->unique()->values()->toArray();

        if (!empty($notifyIds)) {
            $user = Auth::user();
            NotificationService::send(
                $notifyIds,
                'event_comment',
                __('messages.notifications_msg.event_comment', [
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'title' => $event->title,
                ])
            );
        }

        return back()->with('success', __('messages.comments.posted'));
    }

    public function destroy(EventComment $eventComment)
    {
        abort_unless($eventComment->event->club_id === session('current_club_id'), 404);
        abort_unless($eventComment->user_id === Auth::id(), 403);

        $eventComment->delete();

        return back()->with('success', __('messages.comments.deleted'));
    }
}
