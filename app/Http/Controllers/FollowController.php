<?php

namespace App\Http\Controllers;

use App\Events\NewNotification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    public function toggle(User $user)
    {
        $me = Auth::user();

        if ($me->id === $user->id) {
            return back()->with('error', 'Nemůžete sledovat sami sebe.');
        }

        if ($me->isFollowing($user)) {
            $me->following()->detach($user->id);
            return back()->with('success', 'Přestali jste sledovat ' . $user->name);
        }

        $me->following()->attach($user->id);

        broadcast(new NewNotification(
            userId: $user->id,
            type: 'follow',
            message: $me->name . ' vás začal/a sledovat',
            avatar: $me->avatar,
        ));

        return back()->with('success', 'Sledujete ' . $user->name);
    }
}
