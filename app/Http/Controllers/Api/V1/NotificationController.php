<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);
        return response()->json([
            'data' => $notifications->through(fn ($n) => ['id' => $n->id, 'title' => $n->title, 'body' => $n->body, 'type' => $n->type, 'data' => $n->data, 'read_at' => $n->read_at, 'created_at' => $n->created_at]),
            'meta' => ['current_page' => $notifications->currentPage(), 'per_page' => $notifications->perPage(), 'total' => $notifications->total(), 'last_page' => $notifications->lastPage()],
        ]);
    }

    public function unreadCount(Request $request)
    {
        return response()->json(['data' => ['count' => $request->user()->notifications()->unread()->count()]]);
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        abort_unless($notification->user_id === $request->user()->id, 403);
        $notification->markAsRead();
        return response()->json(['message' => 'Marked as read.']);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->notifications()->unread()->update(['read_at' => now()]);
        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
