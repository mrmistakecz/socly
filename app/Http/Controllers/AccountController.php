<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ], [
            'password.current_password' => 'Nesprávné heslo.',
        ]);

        $user = Auth::user();

        // Soft-delete: anonymize personal data, keep posts for DB integrity
        $user->name     = 'Smazaný uživatel';
        $user->username = 'deleted_' . $user->id;
        $user->email    = 'deleted_' . $user->id . '@socly.deleted';
        $user->bio      = null;
        $user->save();

        // Delete avatar and cover
        if ($user->avatar && !str_contains($user->avatar, 'default-avatar')) {
            $this->deleteFile($user->avatar);
        }
        if ($user->cover_image && !str_contains($user->cover_image, 'default-cover')) {
            $this->deleteFile($user->cover_image);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        return redirect('/login')->with('success', 'Váš účet byl smazán.');
    }

    private function deleteFile(string $url): void
    {
        $disk = config('filesystems.default');
        if (str_starts_with($url, '/storage/')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $url));
        } else {
            $key = parse_url($url, PHP_URL_PATH);
            if ($key) Storage::disk($disk)->delete(ltrim($key, '/'));
        }
    }
}
