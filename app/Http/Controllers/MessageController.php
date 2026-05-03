<?php

namespace App\Http\Controllers;

use App\Events\NewMessage;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => ['required', 'exists:users,id'],
            'body' => ['required', 'string', 'max:2000'],
        ], [
            'body.required' => 'Zpráva je povinná.',
            'body.max' => 'Zpráva může mít maximálně 2000 znaků.',
        ]);

        if ((int) $validated['receiver_id'] === Auth::id()) {
            return back()->with('error', 'Nemůžete poslat zprávu sami sobě.');
        }

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $validated['receiver_id'],
            'body' => $validated['body'],
        ]);

        broadcast(new NewMessage($message))->toOthers();

        if ($request->wantsJson()) return response()->json(['success' => true, 'message' => $message]);
        return back()->with('success', 'Zpráva odeslána.');
    }

    public function show(User $user)
    {
        $me = Auth::user();

        $messages = Message::where(function ($q) use ($me, $user) {
            $q->where('sender_id', $me->id)->where('receiver_id', $user->id);
        })->orWhere(function ($q) use ($me, $user) {
            $q->where('sender_id', $user->id)->where('receiver_id', $me->id);
        })->orderBy('created_at', 'asc')
          ->limit(100)
          ->get()
          ->map(fn ($m) => [
              'id' => $m->id,
              'body' => $m->body,
              'isOwn' => $m->sender_id === $me->id,
              'time' => $m->created_at->locale('cs')->format('H:i'),
              'date' => $m->created_at->locale('cs')->isoFormat('D. MMM'),
          ]);

        // Mark as read
        Message::where('sender_id', $user->id)
            ->where('receiver_id', $me->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'messages' => $messages,
            'partner' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'avatar' => $user->avatar,
                'verified' => $user->is_verified,
            ],
        ]);
    }

    public function markRead(User $user)
    {
        Message::where('sender_id', $user->id)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back();
    }
}
