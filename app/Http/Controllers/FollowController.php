<?php

namespace App\Http\Controllers;

use App\Events\NewNotification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function toggle(Request $request, User $user)
    {
        $me = Auth::user();

        if ($me->id === $user->id) {
            if ($request->wantsJson()) return response()->json(['error' => 'Nemůžete sledovat sami sebe.'], 400);
            return back()->with('error', 'Nemůžete sledovat sami sebe.');
        }

        if ($me->isFollowing($user)) {
            $me->following()->detach($user->id);
            if ($request->wantsJson()) return response()->json(['success' => true, 'action' => 'unfollowed']);
            return back()->with('success', 'Přestali jste sledovat ' . $user->name);
        }

        $me->following()->attach($user->id);

        broadcast(new NewNotification(
            userId: $user->id,
            type: 'follow',
            message: $me->name . ' vás začal/a sledovat',
            avatar: $me->avatar,
        ));

        if ($request->wantsJson()) return response()->json(['success' => true, 'action' => 'followed']);
        return back()->with('success', 'Sledujete ' . $user->name);
    }

    public function subscribe(Request $request, User $user)
    {
        $me = Auth::user();

        if ($me->id === $user->id) {
            return response()->json(['error' => 'Nemůžete se přihlásit k odběru sami sebe.'], 400);
        }

        if ($me->isSubscribedTo($user)) {
            return response()->json(['error' => 'Již máte aktivní předplatné.'], 400);
        }

        $price = $user->subscription_price ?? 0;

        $me->subscriptions()->attach($user->id, [
            'price' => $price,
            'expires_at' => now()->addMonth(),
        ]);

        // Auto-follow on subscribe
        if (!$me->isFollowing($user)) {
            $me->following()->attach($user->id);
        }

        broadcast(new NewNotification(
            userId: $user->id,
            type: 'subscription',
            message: $me->name . ' si předplatil/a váš obsah',
            avatar: $me->avatar,
        ));

        return response()->json(['success' => true, 'action' => 'subscribed']);
    }
}
