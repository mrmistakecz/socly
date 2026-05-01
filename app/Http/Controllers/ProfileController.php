<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function show(User $user)
    {
        $me = Auth::user();

        $user->loadCount(['followers', 'following', 'posts']);

        $totalLikes = $user->posts()->sum('likes_count');

        $posts = $user->posts()
            ->withCount(['likes', 'comments'])
            ->latest()
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'image' => $p->image,
                'locked' => $p->is_locked,
                'isVideo' => $p->is_video,
                'likes' => round($p->likes_count / 1000, 1),
            ]);

        return Inertia::render('Profile', [
            'profileUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => '@' . $user->username,
                'avatar' => $user->avatar,
                'cover' => $user->cover_image ?: 'https://images.unsplash.com/photo-1557682250-33bd709cbe85?w=800&h=400&fit=crop',
                'bio' => $user->bio,
                'followers' => $user->followers_count,
                'following' => $user->following_count,
                'likes' => $totalLikes,
                'posts_count' => $user->posts_count,
                'subscriptionPrice' => $user->subscription_price,
                'verified' => $user->is_verified,
                'isVIP' => $user->is_vip,
                'isCreator' => $user->is_creator,
            ],
            'posts' => $posts,
            'isFollowing' => $me ? $me->isFollowing($user) : false,
            'isSubscribed' => $me ? $me->isSubscribedTo($user) : false,
            'isOwn' => $me && $me->id === $user->id,
        ]);
    }

    public function me()
    {
        return $this->show(Auth::user());
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
        ], [
            'name.required' => 'Jméno je povinné.',
            'bio.max' => 'Bio může mít maximálně 500 znaků.',
            'avatar.max' => 'Avatar může mít maximálně 2 MB.',
            'cover_image.max' => 'Cover fotka může mít maximálně 4 MB.',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar && str_starts_with($user->avatar, '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $user->avatar));
            }
            $validated['avatar'] = '/storage/' . $request->file('avatar')->store('avatars', 'public');
        } else {
            unset($validated['avatar']);
        }

        if ($request->hasFile('cover_image')) {
            if ($user->cover_image && str_starts_with($user->cover_image, '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $user->cover_image));
            }
            $validated['cover_image'] = '/storage/' . $request->file('cover_image')->store('covers', 'public');
        } else {
            unset($validated['cover_image']);
        }

        $user->update($validated);

        return back()->with('success', 'Profil byl aktualizován.');
    }

    public function settings()
    {
        return Inertia::render('Settings', [
            'user' => Auth::user(),
        ]);
    }
}
