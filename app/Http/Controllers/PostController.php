<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'caption' => ['nullable', 'string', 'max:2000'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'is_locked' => ['boolean'],
            'price' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ], [
            'caption.max' => 'Popis může mít maximálně 2000 znaků.',
            'image.required' => 'Obrázek je povinný.',
            'image.image' => 'Soubor musí být obrázek.',
            'image.max' => 'Obrázek může mít maximálně 10 MB.',
            'price.max' => 'Cena může být maximálně 100 000.',
        ]);

        // Validate actual file extension
        $file = $request->file('image');
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($extension, $allowedExtensions)) {
            return back()->withErrors(['image' => 'Neplatná přípona souboru.']);
        }

        // Generate secure random filename
        $filename = Str::random(40) . '.' . $extension;
        $disk = config('filesystems.default');
        $path = $file->storeAs('posts', $filename, $disk);

        $imageUrl = $disk === 'public'
            ? '/storage/' . $path
            : Storage::disk($disk)->url($path);

        // Sanitize caption (strip HTML tags)
        $caption = $validated['caption'] ? strip_tags($validated['caption']) : null;

        $post = Post::create([
            'user_id' => Auth::id(),
            'caption' => $caption,
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
        // Check authorization first
        if ($post->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403, 'Nemáte oprávnění smazat tento příspěvek.');
        }

        // Delete file from storage
        if ($post->image) {
            try {
                $disk = config('filesystems.default');
                if (str_starts_with($post->image, '/storage/')) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $post->image));
                } else {
                    $key = parse_url($post->image, PHP_URL_PATH);
                    if ($key) Storage::disk($disk)->delete(ltrim($key, '/'));
                }
            } catch (\Exception $e) {
                \Log::error('Failed to delete post image: ' . $e->getMessage());
            }
        }

        $post->delete();

        if ($request->wantsJson()) return response()->json(['success' => true]);
        return back()->with('success', 'Příspěvek byl smazán.');
    }
}
