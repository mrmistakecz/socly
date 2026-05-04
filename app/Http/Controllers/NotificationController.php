<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()
            ->notifications()
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn($n) => [
                'id'        => $n->id,
                'type'      => $n->data['type'] ?? 'info',
                'message'   => $n->data['message'] ?? '',
                'avatar'    => $n->data['avatar'] ?? null,
                'post_id'   => $n->data['post_id'] ?? null,
                'read'      => !is_null($n->read_at),
                'created_at' => $n->created_at->diffForHumans(),
            ]);

        return response()->json($notifications);
    }

    public function markRead(Request $request)
    {
        $request->validate([
            'id' => ['nullable', 'string'],
        ]);

        $user = Auth::user();

        if ($request->filled('id')) {
            $user->notifications()->where('id', $request->id)->update(['read_at' => now()]);
        } else {
            $user->unreadNotifications()->update(['read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    public function unreadCount()
    {
        return response()->json([
            'count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }
}
