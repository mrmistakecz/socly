<?php

namespace App\Http\Controllers;

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

        Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $validated['receiver_id'],
            'body' => $validated['body'],
        ]);

        return back()->with('success', 'Zpráva odeslána.');
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
