<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Intervention\Image\Laravel\Facades\Image;
use Inertia\Inertia;
use ZipArchive;

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

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ], [
            'current_password.current_password' => 'Současné heslo není správné.',
            'password.min' => 'Heslo musí mít alespoň 8 znaků.',
            'password.mixedCase' => 'Heslo musí obsahovat velká i malá písmena.',
            'password.numbers' => 'Heslo musí obsahovat alespoň jedno číslo.',
        ]);

        $request->user()->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Heslo bylo změněno.');
    }

    public function updateEmail(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'email' => ['required', 'email', 'unique:users,email,' . Auth::id()],
        ], [
            'current_password.current_password' => 'Současné heslo není správné.',
            'email.unique' => 'Tento email je již používán.',
        ]);

        $user = Auth::user();
        $user->pending_email = $request->email;
        $user->save();
        $user->sendEmailVerificationNotification();

        return back()->with('success', 'Na nový email byl odeslán ověřovací odkaz. Po ověření bude email změněn.');
    }

    public function exportData()
    {
        $user = Auth::user();
        $zip = new ZipArchive;
        $zipPath = storage_path('app/tmp/export_' . $user->id . '.zip');
        @mkdir(dirname($zipPath), 0755, true);

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Nepodařilo se vytvořit export.');
        }

        $profile = $user->only(['name', 'username', 'email', 'bio', 'created_at']);
        $zip->addFromString('profile.json', json_encode($profile, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $posts = $user->posts()->get()->map(fn($p) => $p->only(['id', 'caption', 'image', 'is_locked', 'is_video', 'created_at']));
        $zip->addFromString('posts.json', json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $messages = \App\Models\Message::where('sender_id', $user->id)->orWhere('receiver_id', $user->id)->get()
            ->map(fn($m) => $m->only(['id', 'sender_id', 'receiver_id', 'body', 'media', 'created_at']));
        $zip->addFromString('messages.json', json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $zip->close();

        return response()->download($zipPath, 'socly-export-' . $user->username . '.zip')->deleteFileAfterSend();
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
