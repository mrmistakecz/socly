<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function reportUser(Request $request, User $user)
    {
        $request->validate([
            'reason' => ['required', 'string', 'in:spam,harassment,fake,nsfw,other'],
            'notes'  => ['nullable', 'string', 'max:500'],
        ]);

        if ($user->id === Auth::id()) {
            return response()->json(['error' => 'Nemůžete nahlásit sami sebe.'], 400);
        }

        $exists = DB::table('reports')
            ->where('reporter_id', Auth::id())
            ->where('reportable_type', User::class)
            ->where('reportable_id', $user->id)
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'Tohoto uživatele jste již nahlásili.'], 409);
        }

        DB::table('reports')->insert([
            'reporter_id'     => Auth::id(),
            'reportable_type' => User::class,
            'reportable_id'   => $user->id,
            'reason'          => $request->reason,
            'notes'           => $request->notes,
            'status'          => 'pending',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function reportPost(Request $request, Post $post)
    {
        $request->validate([
            'reason' => ['required', 'string', 'in:spam,harassment,fake,nsfw,other'],
            'notes'  => ['nullable', 'string', 'max:500'],
        ]);

        $exists = DB::table('reports')
            ->where('reporter_id', Auth::id())
            ->where('reportable_type', Post::class)
            ->where('reportable_id', $post->id)
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'Tento příspěvek jste již nahlásili.'], 409);
        }

        DB::table('reports')->insert([
            'reporter_id'     => Auth::id(),
            'reportable_type' => Post::class,
            'reportable_id'   => $post->id,
            'reason'          => $request->reason,
            'notes'           => $request->notes,
            'status'          => 'pending',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
