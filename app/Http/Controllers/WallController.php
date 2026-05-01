<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WallController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $posts = Post::with('user')
            ->withCount(['likes', 'comments'])
            ->latest()
            ->limit(20)
            ->get()
            ->map(function ($post) use ($user) {
                return [
                    'id' => $post->id,
                    'creator' => [
                        'id' => $post->user->id,
                        'name' => $post->user->name,
                        'username' => $post->user->username,
                        'avatar' => $post->user->avatar,
                        'verified' => $post->user->is_verified,
                    ],
                    'image' => $post->image,
                    'likes' => $post->likes_count,
                    'comments' => $post->comments_count,
                    'isLocked' => $post->is_locked,
                    'price' => $post->price,
                    'isVideo' => $post->is_video,
                    'caption' => $post->caption,
                    'timeAgo' => $post->created_at->locale('cs')->diffForHumans(),
                    'isLiked' => $user ? $user->hasLiked($post) : false,
                    'isBookmarked' => $user ? $user->hasBookmarked($post) : false,
                ];
            });

        $creators = User::where('is_creator', true)
            ->withCount('followers')
            ->orderByDesc('followers_count')
            ->limit(6)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'avatar' => $c->avatar,
                'hasStory' => true,
                'isVIP' => $c->is_vip,
                'isLive' => false,
            ]);

        $topCreators = User::where('is_creator', true)
            ->withCount('followers')
            ->orderByDesc('followers_count')
            ->limit(4)
            ->get()
            ->map(fn ($c, $i) => [
                'id' => $c->id,
                'name' => $c->name,
                'username' => $c->username,
                'avatar' => $c->avatar,
                'followers' => $this->formatNumber($c->followers_count),
                'verified' => $c->is_verified,
                'badge' => $i + 1,
            ]);

        return Inertia::render('Wall', [
            'posts' => $posts,
            'stories' => $creators,
            'topCreators' => $topCreators,
        ]);
    }

    public function like(Post $post)
    {
        $user = Auth::user();
        $existing = $user->likes()->where('post_id', $post->id)->first();

        if ($existing) {
            $existing->delete();
            $post->decrement('likes_count');
            return back();
        }

        $user->likes()->create(['post_id' => $post->id]);
        $post->increment('likes_count');
        return back();
    }

    public function bookmark(Post $post)
    {
        $user = Auth::user();
        $existing = $user->bookmarks()->where('post_id', $post->id)->first();

        if ($existing) {
            $existing->delete();
            return back();
        }

        $user->bookmarks()->create(['post_id' => $post->id]);
        return back();
    }

    public function comment(Request $request, Post $post)
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $post->comments()->create([
            'user_id' => Auth::id(),
            'body' => $validated['body'],
        ]);

        $post->increment('comments_count');
        return back();
    }

    private function formatNumber(int $num): string
    {
        if ($num >= 1000000) return round($num / 1000000, 1) . 'M';
        if ($num >= 1000) return round($num / 1000, 1) . 'K';
        return (string) $num;
    }
}
