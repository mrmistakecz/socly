<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
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
                'likes' => (int) $p->likes_count,
                'comments' => $p->comments_count,
                'liked' => $me ? $me->hasLiked($p) : false,
                'date' => $p->created_at->locale('cs')->diffForHumans(),
            ]);

        return Inertia::render('Profile', [
            'profileUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => '@' . $user->username,
                'avatar' => $user->avatar,
                'cover' => $user->cover_image ?: '/images/default-cover.svg',
                'bio' => $user->bio,
                'followers' => $user->followers_count,
                'following' => $user->following_count,
                'likes' => $totalLikes,
                'posts_count' => $user->posts_count,
                'subscriptionPrice' => $user->subscription_price,
                'verified' => $user->is_verified,
                'isVIP' => $user->is_vip,
                'isCreator' => $user->is_creator,
                'joinedAt' => $user->created_at->locale('cs')->isoFormat('MMMM YYYY'),
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
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'subscription_price' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ], [
            'name.required' => 'Jméno je povinné.',
            'bio.max' => 'Bio může mít maximálně 500 znaků.',
            'avatar.max' => 'Avatar může mít maximálně 2 MB.',
            'cover_image.max' => 'Cover fotka může mít maximálně 4 MB.',
            'subscription_price.max' => 'Cena předplatného může být maximálně 100 000.',
        ]);

        // Sanitize bio (strip HTML tags)
        if (isset($validated['bio'])) {
            $validated['bio'] = strip_tags($validated['bio']);
        }

        $disk = config('filesystems.default');

        if ($request->hasFile('avatar')) {
            // Validate file extension
            $file = $request->file('avatar');
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $extension = strtolower($file->getClientOriginalExtension());
            
            if (!in_array($extension, $allowedExtensions)) {
                return back()->withErrors(['avatar' => 'Neplatná přípona souboru.']);
            }

            if ($user->avatar && $user->avatar !== '/images/default-avatar.svg') {
                $this->deleteOldFile($user->avatar, $disk);
            }

            // Sanitize: re-encode (strip EXIF), resize to max 400px square
            $filename = Str::random(40) . '.jpg';
            $img = Image::read($file)->scaleDown(width: 400);
            Storage::disk($disk)->put('avatars/' . $filename, $img->toJpeg(quality: 85)->toString());
            $path = 'avatars/' . $filename;
            $validated['avatar'] = $disk === 'public' ? '/storage/' . $path : Storage::disk($disk)->url($path);
        } else {
            unset($validated['avatar']);
        }

        if ($request->hasFile('cover_image')) {
            // Validate file extension
            $file = $request->file('cover_image');
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $extension = strtolower($file->getClientOriginalExtension());
            
            if (!in_array($extension, $allowedExtensions)) {
                return back()->withErrors(['cover_image' => 'Neplatná přípona souboru.']);
            }

            if ($user->cover_image && $user->cover_image !== '/images/default-cover.svg') {
                $this->deleteOldFile($user->cover_image, $disk);
            }

            // Sanitize: re-encode (strip EXIF), resize to max 1200px
            $filename = Str::random(40) . '.jpg';
            $img = Image::read($file)->scaleDown(width: 1200);
            Storage::disk($disk)->put('covers/' . $filename, $img->toJpeg(quality: 80)->toString());
            $path = 'covers/' . $filename;
            $validated['cover_image'] = $disk === 'public' ? '/storage/' . $path : Storage::disk($disk)->url($path);
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

    private function deleteOldFile(string $url, string $disk): void
    {
        if (str_starts_with($url, '/storage/')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $url));
        } elseif (str_starts_with($url, 'http')) {
            $key = parse_url($url, PHP_URL_PATH);
            if ($key) Storage::disk($disk)->delete(ltrim($key, '/'));
        }
    }
}
