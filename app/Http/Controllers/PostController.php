<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'caption' => ['nullable', 'string', 'max:2000'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'is_locked' => ['boolean'],
            'price' => ['nullable', 'integer', 'min:0'],
        ], [
            'caption.max' => 'Popis může mít maximálně 2000 znaků.',
            'image.required' => 'Obrázek je povinný.',
            'image.image' => 'Soubor musí být obrázek.',
            'image.max' => 'Obrázek může mít maximálně 10 MB.',
        ]);

        $disk = config('filesystems.default');
        $path = $request->file('image')->store('posts', $disk);

        $imageUrl = $disk === 'public'
            ? '/storage/' . $path
            : Storage::disk($disk)->url($path);

        $post = Post::create([
            'user_id' => Auth::id(),
            'caption' => $validated['caption'] ?? null,
            'image' => $imageUrl,
            'is_locked' => $validated['is_locked'] ?? false,
            'price' => $validated['price'] ?? null,
        ]);

        if ($request->wantsJson()) {
            $user = Auth::user();
            return response()->json([
                'success' => true,
                'post' => [
                    'id' => $post->id,
                    'creator' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                        'avatar' => $user->avatar,
                        'verified' => $user->is_verified,
                    ],
                    'image' => $post->image,
                    'likes' => 0,
                    'comments' => 0,
                    'isLocked' => $post->is_locked,
                    'price' => $post->price,
                    'isVideo' => $post->is_video,
                    'caption' => $post->caption,
                    'timeAgo' => 'právě teď',
                    'isLiked' => false,
                    'isBookmarked' => false,
                    'recentComments' => [],
                ]
            ]);
        }

        return redirect('/?tab=home')->with('success', 'Příspěvek byl vytvořen!');
    }

    public function destroy(Request $request, Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            abort(403);
        }

        if ($post->image) {
            $disk = config('filesystems.default');
            if (str_starts_with($post->image, '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $post->image));
            } else {
                $key = parse_url($post->image, PHP_URL_PATH);
                if ($key) Storage::disk($disk)->delete(ltrim($key, '/'));
            }
        }

        $post->delete();

        if ($request->wantsJson()) return response()->json(['success' => true]);
        return back()->with('success', 'Příspěvek byl smazán.');
    }
}
