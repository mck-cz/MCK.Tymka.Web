<?php

namespace App\Http\Controllers;

use App\Models\ClubMembership;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $conversations = Conversation::whereHas('conversationParticipants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->with(['conversationParticipants.user', 'messages' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->get()
            ->sortByDesc(function ($conv) {
                return $conv->messages->first()?->created_at ?? $conv->created_at;
            });

        return view('messages.index', compact('conversations'));
    }

    public function create(Request $request)
    {
        $clubId = session('current_club_id');

        // Get users in the same club for the recipient picker
        $users = User::whereHas('clubMemberships', function ($q) use ($clubId) {
            $q->where('club_id', $clubId)->where('status', 'active');
        })
            ->where('id', '!=', auth()->id())
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('messages.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'body' => 'required|string|max:5000',
        ]);

        $userId = auth()->id();
        $recipientId = $request->input('recipient_id');

        // Verify recipient is in the same club
        $clubId = session('current_club_id');
        $recipientInClub = ClubMembership::where('club_id', $clubId)
            ->where('user_id', $recipientId)
            ->where('status', 'active')
            ->exists();
        abort_unless($recipientInClub, 403);

        // Check if conversation already exists between these two users
        $conversation = Conversation::whereHas('conversationParticipants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->whereHas('conversationParticipants', function ($q) use ($recipientId) {
                $q->where('user_id', $recipientId);
            })
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create();

            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $userId,
                'joined_at' => now(),
            ]);

            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $recipientId,
                'joined_at' => now(),
            ]);
        }

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $userId,
            'body' => $request->input('body'),
        ]);

        return redirect()->route('messages.show', $conversation);
    }

    public function show(Conversation $conversation)
    {
        $userId = auth()->id();

        // Verify user is participant
        $participant = ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $userId)
            ->first();

        abort_unless($participant, 403);

        // Mark as read
        $participant->update(['last_read_at' => now()]);

        $messages = $conversation->messages()
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        $otherParticipant = $conversation->conversationParticipants()
            ->where('user_id', '!=', $userId)
            ->with('user')
            ->first();

        return view('messages.show', compact('conversation', 'messages', 'otherParticipant'));
    }

    public function reply(Request $request, Conversation $conversation)
    {
        $userId = auth()->id();

        $participant = ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $userId)
            ->first();

        abort_unless($participant, 403);

        $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $userId,
            'body' => $request->input('body'),
        ]);

        $participant->update(['last_read_at' => now()]);

        return redirect()->route('messages.show', $conversation);
    }
}
