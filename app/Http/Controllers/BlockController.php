<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BlockController extends Controller
{
    public function toggle(User $user)
    {
        $me = Auth::id();

        if ($user->id === $me) {
            return response()->json(['error' => 'Nemůžete zablokovat sami sebe.'], 400);
        }

        $exists = DB::table('blocks')
            ->where('blocker_id', $me)
            ->where('blocked_id', $user->id)
            ->exists();

        if ($exists) {
            DB::table('blocks')->where('blocker_id', $me)->where('blocked_id', $user->id)->delete();
            return response()->json(['action' => 'unblocked']);
        }

        DB::table('blocks')->insert([
            'blocker_id' => $me,
            'blocked_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('follows')
            ->where(function ($q) use ($me, $user) {
                $q->where('follower_id', $me)->where('following_id', $user->id);
            })
            ->orWhere(function ($q) use ($me, $user) {
                $q->where('follower_id', $user->id)->where('following_id', $me);
            })
            ->delete();

        return response()->json(['action' => 'blocked']);
    }
}
