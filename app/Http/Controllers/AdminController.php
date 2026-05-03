<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'totalUsers' => User::count(),
            'totalPosts' => Post::count(),
            'totalMessages' => Message::count(),
            'newUsersToday' => User::whereDate('created_at', today())->count(),
            'newPostsToday' => Post::whereDate('created_at', today())->count(),
        ];

        $users = User::withCount(['posts', 'followers', 'following'])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'username' => $u->username,
                'email' => $u->email,
                'avatar' => $u->avatar,
                'is_verified' => $u->is_verified,
                'is_vip' => $u->is_vip,
                'is_creator' => $u->is_creator,
                'is_admin' => $u->is_admin,
                'posts_count' => $u->posts_count,
                'followers_count' => $u->followers_count,
                'following_count' => $u->following_count,
                'created_at' => $u->created_at->format('d.m.Y H:i'),
            ]);

        $posts = Post::with('user')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'image' => $p->image,
                'caption' => $p->caption,
                'likes_count' => $p->likes_count,
                'comments_count' => $p->comments_count,
                'is_locked' => $p->is_locked,
                'user_name' => $p->user->name,
                'user_id' => $p->user->id,
                'created_at' => $p->created_at->format('d.m.Y H:i'),
            ]);

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'users' => $users,
            'posts' => $posts,
        ]);
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'is_verified' => ['sometimes', 'boolean'],
            'is_vip' => ['sometimes', 'boolean'],
            'is_creator' => ['sometimes', 'boolean'],
            'is_admin' => ['sometimes', 'boolean'],
        ]);

        $user->update($validated);

        return back()->with('success', "Uživatel {$user->name} byl aktualizován.");
    }

    public function deleteUser(User $user)
    {
        if ($user->is_admin) {
            return back()->with('error', 'Nelze smazat admina.');
        }

        $user->posts()->delete();
        $user->likes()->delete();
        $user->comments()->delete();
        $user->bookmarks()->delete();
        $user->sentMessages()->delete();
        $user->receivedMessages()->delete();
        DB::table('follows')->where('follower_id', $user->id)->orWhere('following_id', $user->id)->delete();
        $user->delete();

        return back()->with('success', 'Uživatel byl smazán.');
    }

    public function deletePost(Post $post)
    {
        $post->likes()->delete();
        $post->comments()->delete();
        $post->bookmarks()->delete();
        $post->delete();

        return back()->with('success', 'Příspěvek byl smazán.');
    }
}
