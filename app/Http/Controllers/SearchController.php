<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (mb_strlen($q) < 2 || mb_strlen($q) > 100) {
            return response()->json(['users' => [], 'posts' => []]);
        }

        $escaped = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $q);

        $users = User::where('name', 'ilike', "%{$escaped}%")
            ->orWhere('username', 'ilike', "%{$escaped}%")
            ->withCount('followers')
            ->limit(10)
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'username' => $u->username,
                'avatar' => $u->avatar,
                'verified' => $u->is_verified,
                'followers' => $u->followers_count,
            ]);

        return response()->json(['users' => $users]);
    }
}
