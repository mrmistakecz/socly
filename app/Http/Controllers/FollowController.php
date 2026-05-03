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
}
