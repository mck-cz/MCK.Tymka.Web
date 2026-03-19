<?php

namespace App\Http\Controllers;

use App\Models\CalendarFeed;
use App\Models\Event;
use App\Models\Team;
use App\Models\TeamMembership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CalendarFeedController extends Controller
{
    public function index()
    {
        $feeds = CalendarFeed::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        $clubId = session('current_club_id');
        $teamIds = TeamMembership::where('user_id', Auth::id())
            ->whereHas('team', fn ($q) => $q->where('club_id', $clubId))
            ->pluck('team_id');
        $teams = Team::whereIn('id', $teamIds)->orderBy('name')->get();

        return view('calendar-feeds.index', compact('feeds', 'teams'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'include_teams' => 'nullable|array',
            'include_teams.*' => 'uuid',
            'include_event_types' => 'nullable|array',
            'include_event_types.*' => 'in:training,match,competition,tournament',
        ]);

        CalendarFeed::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'token' => Str::random(64),
            'include_teams' => $validated['include_teams'] ?? [],
            'include_event_types' => $validated['include_event_types'] ?? [],
            'is_default' => false,
            'is_active' => true,
        ]);

        return back()->with('success', __('messages.calendar_feeds.created'));
    }

    public function toggleActive(CalendarFeed $calendarFeed)
    {
        abort_unless($calendarFeed->user_id === Auth::id(), 403);

        $newToken = $calendarFeed->is_active ? $calendarFeed->token : Str::random(64);

        $calendarFeed->update([
            'is_active' => !$calendarFeed->is_active,
            'token' => $newToken,
        ]);

        return back()->with('success', __('messages.calendar_feeds.toggled'));
    }

    public function destroy(CalendarFeed $calendarFeed)
    {
        abort_unless($calendarFeed->user_id === Auth::id(), 403);

        $calendarFeed->delete();

        return back()->with('success', __('messages.calendar_feeds.deleted'));
    }

    /**
     * Public iCal feed endpoint (no auth, token-based).
     */
    public function ical(string $token)
    {
        $feed = CalendarFeed::where('token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        $user = $feed->user;

        // Get team IDs for this feed
        $teamIds = !empty($feed->include_teams)
            ? $feed->include_teams
            : TeamMembership::where('user_id', $user->id)
                ->pluck('team_id')
                ->toArray();

        // Build events query
        $query = Event::whereIn('team_id', $teamIds)
            ->where('status', 'scheduled')
            ->with(['team', 'venue']);

        // Filter by event types
        if (!empty($feed->include_event_types)) {
            $query->whereIn('event_type', $feed->include_event_types);
        }

        // Limit to reasonable range
        $query->where('starts_at', '>=', now()->subMonths(3))
            ->where('starts_at', '<=', now()->addMonths(6));

        $events = $query->orderBy('starts_at')->get();

        // Generate iCal
        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//Tymka//Calendar//EN\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";
        $ical .= "X-WR-CALNAME:" . $this->escapeIcal($feed->name) . "\r\n";

        foreach ($events as $event) {
            $ical .= "BEGIN:VEVENT\r\n";
            $ical .= "UID:" . $event->id . "@tymka\r\n";
            $ical .= "DTSTART:" . $event->starts_at->format('Ymd\THis') . "\r\n";
            if ($event->ends_at) {
                $ical .= "DTEND:" . $event->ends_at->format('Ymd\THis') . "\r\n";
            }
            $ical .= "SUMMARY:" . $this->escapeIcal($event->title . ' — ' . $event->team->name) . "\r\n";

            $location = $event->venue?->name ?? $event->location;
            if ($location) {
                $ical .= "LOCATION:" . $this->escapeIcal($location) . "\r\n";
            }

            if ($event->notes) {
                $ical .= "DESCRIPTION:" . $this->escapeIcal($event->notes) . "\r\n";
            }

            $ical .= "STATUS:CONFIRMED\r\n";
            $ical .= "END:VEVENT\r\n";
        }

        $ical .= "END:VCALENDAR\r\n";

        return response($ical, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="tymka.ics"',
        ]);
    }

    private function escapeIcal(string $text): string
    {
        $text = str_replace(['\\', ';', ',', "\n", "\r"], ['\\\\', '\\;', '\\,', '\\n', ''], $text);
        return $text;
    }
}
