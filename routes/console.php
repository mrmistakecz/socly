<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('subscriptions:expire', function () {
    $count = DB::table('subscriptions')
        ->where('expires_at', '<', now())
        ->delete();
    $this->info("Expired {$count} subscriptions.");
})->purpose('Remove expired subscriptions');

Artisan::command('stories:cleanup', function () {
    $disk = config('filesystems.default');
    $stories = \App\Models\Story::where('expires_at', '<', now())->get();
    foreach ($stories as $story) {
        if (str_starts_with($story->media_url, '/storage/')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $story->media_url));
        } else {
            $key = parse_url($story->media_url, PHP_URL_PATH);
            if ($key) Storage::disk($disk)->delete(ltrim($key, '/'));
        }
        $story->delete();
    }
    $this->info("Cleaned up {$stories->count()} expired stories.");
})->purpose('Delete expired stories and their media files');

Artisan::command('posts:sync-counts', function () {
    DB::statement('UPDATE posts SET likes_count = (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id)');
    DB::statement('UPDATE posts SET comments_count = (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id)');
    $this->info('Post counts synced.');
})->purpose('Synchronize likes_count and comments_count with actual DB counts');

Schedule::command('subscriptions:expire')->daily();
Schedule::command('stories:cleanup')->hourly();
Schedule::command('posts:sync-counts')->hourly();
