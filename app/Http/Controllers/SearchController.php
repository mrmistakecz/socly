<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q', '');

        if (strlen($q) < 2) {
            return response()->json(['users' => [], 'posts' => []]);
        }

        $users = User::where('name', 'like', "%{$q}%")
            ->orWhere('username', 'like', "%{$q}%")
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
