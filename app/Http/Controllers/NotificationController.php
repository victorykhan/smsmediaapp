<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->latest()->paginate(20);
        return view('notifications.index', compact('notifications'));
    }

    public function markRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['success' => true]);
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read.');
    }

    public function unread()
    {
        $count = auth()->user()->unreadNotifications()->count();
        $items = auth()->user()->unreadNotifications()->latest()->take(5)->get()->map(fn($n) => [
            'id' => $n->id,
            'message' => $n->data['message'] ?? '',
            'action_url' => $n->data['action_url'] ?? '#',
            'created_at' => $n->created_at->diffForHumans(),
        ]);
        return response()->json(['count' => $count, 'items' => $items]);
    }
}
