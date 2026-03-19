<?php

namespace App\Http\Controllers;

use App\Models\ClubMembership;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Team;
use App\Models\TeamMembership;
use App\Models\TeamPost;
use App\Models\TeamPostAttachment;
use App\Models\TeamPostComment;
use App\Services\ImageService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TeamPostController extends Controller
{
    public function index(Team $team)
    {
        $clubId = session('current_club_id');
        abort_unless($team->club_id === $clubId, 404);

        $posts = TeamPost::where('team_id', $team->id)
            ->with(['user', 'teamPostComments.user', 'pollOptions.pollVotes', 'attachments'])
            ->orderBy('created_at', 'desc')
            ->get();

        $userId = Auth::id();
        $clubId = session('current_club_id');

        $isDirectMember = TeamMembership::where('team_id', $team->id)
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->exists();

        // Can post: club admin, head_coach, or assistant_coach on this team
        $isClubAdmin = ClubMembership::where('club_id', $clubId)
            ->where('user_id', $userId)
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->exists();

        $isCoachOnTeam = TeamMembership::where('team_id', $team->id)
            ->where('user_id', $userId)
            ->whereIn('role', ['head_coach', 'assistant_coach'])
            ->where('status', 'active')
            ->exists();

        $canPost = $isClubAdmin || $isCoachOnTeam;

        return view('teams.wall', compact('team', 'posts', 'isDirectMember', 'canPost'));
    }

    public function store(Request $request, Team $team)
    {
        $clubId = session('current_club_id');
        abort_unless($team->club_id === $clubId, 404);

        $this->authorizeCoachOrAdmin($team);

        $validated = $request->validate([
            'body' => 'required|string|max:50000',
            'post_type' => 'required|in:message,poll',
            'poll_options' => 'required_if:post_type,poll|array|min:2|max:10',
            'poll_options.*' => 'required|string|max:255',
            'attachment_ids' => 'nullable|array',
            'attachment_ids.*' => 'uuid',
        ]);

        // Sanitize HTML body — allow only Trix-generated tags
        $body = strip_tags($validated['body'], [
            'div', 'br', 'strong', 'em', 'del', 'a', 'ul', 'ol', 'li',
            'blockquote', 'pre', 'h1', 'figure', 'figcaption', 'img',
        ]);

        $post = TeamPost::create([
            'team_id' => $team->id,
            'user_id' => Auth::id(),
            'body' => $body,
            'post_type' => $validated['post_type'],
        ]);

        // Link uploaded attachments to this post
        if (!empty($validated['attachment_ids'])) {
            TeamPostAttachment::whereIn('id', $validated['attachment_ids'])
                ->where('uploaded_by', Auth::id())
                ->whereNull('post_id')
                ->update(['post_id' => $post->id]);
        }

        if ($validated['post_type'] === 'poll' && !empty($validated['poll_options'])) {
            foreach ($validated['poll_options'] as $i => $label) {
                if (trim($label) !== '') {
                    PollOption::create([
                        'post_id' => $post->id,
                        'label' => trim($label),
                        'sort_order' => $i,
                    ]);
                }
            }
        }

        // Notify all team members except the post author
        $memberUserIds = TeamMembership::where('team_id', $team->id)
            ->where('status', 'active')
            ->where('user_id', '!=', Auth::id())
            ->pluck('user_id')
            ->toArray();

        if (!empty($memberUserIds)) {
            $user = Auth::user();
            NotificationService::send(
                $memberUserIds,
                'wall_post',
                __('messages.notifications_msg.wall_post', [
                    'name' => $user->first_name . ' ' . $user->last_name,
                ])
            );
        }

        return back()->with('success', __('messages.wall.posted'));
    }

    public function uploadAttachment(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $isImage = str_starts_with($file->getMimeType(), 'image/');

        if ($isImage) {
            $path = ImageService::store($file, 'wall-attachments', 1920, 1920, 80);
            $mime = 'image/jpeg';
            $fileSize = Storage::disk('public')->size($path);
        } else {
            $path = $file->store('wall-attachments', 'public');
            $mime = $file->getMimeType();
            $fileSize = $file->getSize();
        }

        $attachment = TeamPostAttachment::create([
            'uploaded_by' => Auth::id(),
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $mime,
            'file_size' => $fileSize,
        ]);

        $url = Storage::disk('public')->url($path);

        return response()->json([
            'id' => $attachment->id,
            'url' => $url,
            'href' => $url,
            'filename' => $attachment->original_name,
            'filesize' => $attachment->file_size,
            'content_type' => $attachment->mime_type,
        ]);
    }

    public function storeComment(Request $request, TeamPost $teamPost)
    {
        $clubId = session('current_club_id');
        $team = $teamPost->team;
        abort_unless($team->club_id === $clubId, 404);

        $this->authorizeTeamMember($team);

        $validated = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        TeamPostComment::create([
            'post_id' => $teamPost->id,
            'user_id' => Auth::id(),
            'body' => $validated['body'],
        ]);

        // Notify post author + other commenters, excluding comment author
        $postAuthorId = collect([$teamPost->user_id]);
        $commenterIds = TeamPostComment::where('post_id', $teamPost->id)
            ->where('user_id', '!=', Auth::id())
            ->distinct()
            ->pluck('user_id');

        $notifyIds = $postAuthorId->merge($commenterIds)
            ->unique()
            ->reject(fn ($id) => $id === Auth::id())
            ->values()
            ->toArray();

        if (!empty($notifyIds)) {
            $user = Auth::user();
            NotificationService::send(
                $notifyIds,
                'wall_comment',
                __('messages.notifications_msg.wall_comment', [
                    'name' => $user->first_name . ' ' . $user->last_name,
                ])
            );
        }

        return back()->with('success', __('messages.wall.comment_posted'));
    }

    public function vote(Request $request, PollOption $pollOption)
    {
        $teamPost = $pollOption->teamPost;
        $clubId = session('current_club_id');
        abort_unless($teamPost->team->club_id === $clubId, 404);

        $userId = Auth::id();

        // Remove existing votes on this poll
        $optionIds = PollOption::where('post_id', $teamPost->id)->pluck('id');
        PollVote::whereIn('option_id', $optionIds)
            ->where('user_id', $userId)
            ->delete();

        // Add new vote
        PollVote::create([
            'option_id' => $pollOption->id,
            'user_id' => $userId,
        ]);

        return back()->with('success', __('messages.wall.voted'));
    }

    public function destroy(TeamPost $teamPost)
    {
        abort_unless($teamPost->user_id === Auth::id(), 403);

        // Delete attachment files
        foreach ($teamPost->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }
        $teamPost->attachments()->delete();

        $teamPost->teamPostComments()->delete();
        $optionIds = $teamPost->pollOptions()->pluck('id');
        PollVote::whereIn('option_id', $optionIds)->delete();
        $teamPost->pollOptions()->delete();
        $teamPost->delete();

        return back()->with('success', __('messages.wall.deleted'));
    }

    private function authorizeTeamMember(Team $team): void
    {
        $exists = TeamMembership::where('team_id', $team->id)
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->exists();

        abort_unless($exists, 403);
    }

    private function authorizeCoachOrAdmin(Team $team): void
    {
        $userId = Auth::id();
        $clubId = session('current_club_id');

        $isClubAdmin = ClubMembership::where('club_id', $clubId)
            ->where('user_id', $userId)
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->exists();

        if ($isClubAdmin) {
            return;
        }

        $isCoach = TeamMembership::where('team_id', $team->id)
            ->where('user_id', $userId)
            ->whereIn('role', ['head_coach', 'assistant_coach'])
            ->where('status', 'active')
            ->exists();

        abort_unless($isCoach, 403);
    }
}
