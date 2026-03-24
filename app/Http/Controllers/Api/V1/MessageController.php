<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->input('filter', 'inbox');
        $query = $filter === 'sent' ? Message::sent($request->user()->id) : Message::inbox($request->user()->id);

        $messages = $query->with('sender:id,name,avatar')->with('receiver:id,name,avatar')->with('course:id,title')->latest()->paginate(20);

        return response()->json([
            'data' => $messages->through(fn ($m) => [
                'id' => $m->id, 'sender' => $m->sender, 'receiver' => $m->receiver,
                'course' => $m->course, 'subject' => $m->subject, 'body' => $m->body,
                'is_read' => $m->is_read, 'read_at' => $m->read_at, 'created_at' => $m->created_at,
            ]),
            'meta' => ['current_page' => $messages->currentPage(), 'per_page' => $messages->perPage(), 'total' => $messages->total()],
        ]);
    }

    public function unreadCount(Request $request)
    {
        return response()->json(['data' => ['count' => Message::inbox($request->user()->id)->unread()->count()]]);
    }

    public function conversations(Request $request)
    {
        $userId = $request->user()->id;
        $conversations = Message::where('sender_id', $userId)->orWhere('receiver_id', $userId)
            ->latest()->get()
            ->groupBy(fn ($msg) => $msg->sender_id === $userId ? $msg->receiver_id : $msg->sender_id)
            ->map(function ($messages, $otherUserId) {
                $latest = $messages->first();
                return [
                    'user' => User::select('id', 'name', 'avatar', 'role')->find($otherUserId),
                    'last_message' => ['id' => $latest->id, 'body' => $latest->body, 'created_at' => $latest->created_at, 'is_read' => $latest->is_read],
                    'unread_count' => $messages->where('is_read', false)->count(),
                ];
            })->values();

        return response()->json(['data' => $conversations]);
    }

    public function conversation(Request $request, User $user)
    {
        $messages = Message::conversationWith($request->user()->id, $user->id)
            ->with('sender:id,name,avatar')->with('course:id,title')->oldest()->paginate(50);

        Message::where('sender_id', $user->id)->where('receiver_id', $request->user()->id)->unread()->update(['is_read' => true, 'read_at' => now()]);

        return response()->json([
            'data' => $messages->through(fn ($m) => [
                'id' => $m->id, 'sender' => $m->sender, 'course' => $m->course,
                'subject' => $m->subject, 'body' => $m->body, 'is_read' => $m->is_read,
                'read_at' => $m->read_at, 'created_at' => $m->created_at,
            ]),
            'meta' => ['current_page' => $messages->currentPage(), 'per_page' => $messages->perPage(), 'total' => $messages->total()],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'course_id' => 'nullable|exists:courses,id',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string|max:5000',
        ]);

        $sender = $request->user();
        $receiver = User::findOrFail($validated['receiver_id']);

        if ($sender->role === 'student') {
            abort_unless($receiver->role === 'instructor', 422, 'Students can only message instructors.');
            abort_unless(Enrollment::where('user_id', $sender->id)->whereHas('course', fn ($q) => $q->where('instructor_id', $receiver->id))->exists(), 403, 'You can only message instructors of your enrolled courses.');
        }
        if ($sender->role === 'instructor') {
            abort_unless($receiver->role === 'student', 422, 'Instructors can only message their students.');
            abort_unless(Enrollment::where('user_id', $receiver->id)->whereHas('course', fn ($q) => $q->where('instructor_id', $sender->id))->exists(), 403, 'You can only message students enrolled in your courses.');
        }

        $message = Message::create(['sender_id' => $sender->id, 'receiver_id' => $validated['receiver_id'], 'course_id' => $validated['course_id'] ?? null, 'subject' => $validated['subject'] ?? null, 'body' => $validated['body']]);

        \App\Models\Notification::create(['user_id' => $receiver->id, 'title' => 'New Message', 'body' => "{$sender->name} sent you a message", 'type' => 'system', 'data' => ['message_id' => $message->id, 'sender_id' => $sender->id]]);

        return response()->json(['data' => $message->load('sender:id,name,avatar'), 'message' => 'Message sent.'], 201);
    }

    public function markAsRead(Request $request, Message $message)
    {
        abort_unless($message->receiver_id === $request->user()->id, 403);
        $message->markAsRead();
        return response()->json(['message' => 'Marked as read.']);
    }
}
