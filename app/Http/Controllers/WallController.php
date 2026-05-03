<?php

namespace App\Http\Controllers;

use App\Events\NewNotification;
use App\Events\PostInteraction;
use App\Models\Post;
use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class WallController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $sort = $request->get('sort', 'latest');

        $postsQuery = Post::with(['user', 'comments.user'])
            ->withCount(['likes', 'comments']);

        if ($sort === 'trending') {
            $postsQuery->orderByDesc('likes_count');
        } else {
            $postsQuery->latest();
        }

        $posts = $postsQuery->limit(20)
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
                    'recentComments' => $post->comments->sortByDesc('created_at')->take(5)->map(fn ($c) => [
                        'id' => $c->id,
                        'body' => $c->body,
                        'user' => [
                            'name' => $c->user->name,
                            'avatar' => $c->user->avatar,
                        ],
                        'timeAgo' => $c->created_at->locale('cs')->diffForHumans(),
                    ])->values(),
                ];
            });

        $creators = User::where('id', '!=', $user?->id)
            ->withCount('followers')
            ->orderByDesc('followers_count')
            ->limit(8)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'avatar' => $c->avatar,
                'hasStory' => true,
                'isVIP' => $c->is_vip,
                'isLive' => false,
            ]);

        $topCreators = User::where('id', '!=', $user?->id)
            ->withCount('followers')
            ->orderByDesc('followers_count')
            ->limit(10)
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

        // Conversations: get unique users the current user has exchanged messages with
        $conversations = collect();
        if ($user) {
            $partnerIds = Message::where('sender_id', $user->id)
                ->orWhere('receiver_id', $user->id)
                ->selectRaw('CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END as partner_id', [$user->id])
                ->distinct()
                ->pluck('partner_id');

            $conversations = User::whereIn('id', $partnerIds)->get()->map(function ($partner) use ($user) {
                $lastMsg = Message::where(function ($q) use ($user, $partner) {
                    $q->where('sender_id', $user->id)->where('receiver_id', $partner->id);
                })->orWhere(function ($q) use ($user, $partner) {
                    $q->where('sender_id', $partner->id)->where('receiver_id', $user->id);
                })->latest()->first();

                $unread = Message::where('sender_id', $partner->id)
                    ->where('receiver_id', $user->id)
                    ->where('is_read', false)
                    ->count();

                return [
                    'id' => $partner->id,
                    'name' => $partner->name,
                    'username' => $partner->username,
                    'avatar' => $partner->avatar,
                    'verified' => $partner->is_verified,
                    'isVIP' => $partner->is_vip,
                    'isOnline' => false,
                    'lastMessage' => $lastMsg?->body ?? '',
                    'time' => $lastMsg ? $lastMsg->created_at->locale('cs')->diffForHumans(short: true) : '',
                    'unread' => $unread,
                    'hasMedia' => false,
                ];
            })->sortByDesc('unread')->values();
        }

        $trendingPosts = Post::with('user')
            ->orderByDesc('posts.likes_count')
            ->limit(9)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'image' => $p->image,
                'isLocked' => $p->is_locked,
                'isVideo' => $p->is_video,
                'likes' => $this->formatNumber($p->likes_count),
                'caption' => $p->caption,
                'creator' => ['id' => $p->user->id],
            ]);

        return Inertia::render('Wall', [
            'posts' => $posts,
            'stories' => $creators,
            'topCreators' => $topCreators,
            'trendingPosts' => $trendingPosts,
            'conversations' => $conversations,
        ]);
    }

    public function postsApi(Request $request)
    {
        $user = Auth::user();
        $sort = $request->get('sort', 'latest');
        $page = (int) $request->get('page', 1);
        $limit = 20;

        $postsQuery = Post::with(['user', 'comments.user'])
            ->withCount(['likes', 'comments']);

        if ($sort === 'trending') {
            $postsQuery->orderByDesc('likes_count');
        } else {
            $postsQuery->latest();
        }

        $posts = $postsQuery
            ->offset(($page - 1) * $limit)
            ->limit($limit)
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
                    'recentComments' => $post->comments->sortByDesc('created_at')->take(5)->map(fn ($c) => [
                        'id' => $c->id,
                        'body' => $c->body,
                        'user' => [
                            'name' => $c->user->name,
                            'avatar' => $c->user->avatar,
                        ],
                        'timeAgo' => $c->created_at->locale('cs')->diffForHumans(),
                    ])->values(),
                ];
            });

        return response()->json(['posts' => $posts]);
    }

    public function like(Request $request, Post $post)
    {
        $user = Auth::user();
        $existing = $user->likes()->where('post_id', $post->id)->first();

        if ($existing) {
            $existing->delete();
            $post->decrement('likes_count');
            broadcast(new PostInteraction($post->id, 'likes', $post->likes_count - 1))->toOthers();
            if ($request->wantsJson()) return response()->json(['success' => true, 'action' => 'unliked']);
            return back();
        }

        $user->likes()->create(['post_id' => $post->id]);
        $post->increment('likes_count');
        broadcast(new PostInteraction($post->id, 'likes', $post->likes_count + 1))->toOthers();

        if ($post->user_id !== $user->id) {
            broadcast(new NewNotification(
                userId: $post->user_id,
                type: 'like',
                message: $user->name . ' dal/a like vašemu příspěvku',
                avatar: $user->avatar,
                postId: $post->id,
            ));
        }

        if ($request->wantsJson()) return response()->json(['success' => true, 'action' => 'liked']);
        return back();
    }

    public function bookmark(Request $request, Post $post)
    {
        $user = Auth::user();
        $existing = $user->bookmarks()->where('post_id', $post->id)->first();

        if ($existing) {
            $existing->delete();
            if ($request->wantsJson()) return response()->json(['success' => true, 'action' => 'unbookmarked']);
            return back();
        }

        $user->bookmarks()->create(['post_id' => $post->id]);
        
        if ($request->wantsJson()) return response()->json(['success' => true, 'action' => 'bookmarked']);
        return back();
    }

    public function comment(Request $request, Post $post)
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $user = Auth::user();

        $post->comments()->create([
            'user_id' => $user->id,
            'body' => $validated['body'],
        ]);

        $post->increment('comments_count');
        broadcast(new PostInteraction($post->id, 'comments', $post->comments_count + 1))->toOthers();

        if ($post->user_id !== $user->id) {
            broadcast(new NewNotification(
                userId: $post->user_id,
                type: 'comment',
                message: $user->name . ' komentoval/a váš příspěvek',
                avatar: $user->avatar,
                postId: $post->id,
            ));
        }

        if ($request->wantsJson()) return response()->json(['success' => true]);
        return back();
    }

    public function discover(Request $request)
    {
        $category = $request->get('category', 'all');

        $query = Post::with('user');

        switch ($category) {
            case 'trending':
                $query->orderByDesc('likes_count');
                break;
            case 'popular':
                $query->where('likes_count', '>=', 1)->orderByDesc('comments_count');
                break;
            case 'new':
                $query->orderByDesc('created_at');
                break;
            case 'vip':
                $query->whereHas('user', fn ($q) => $q->where('is_vip', true));
                break;
            default:
                $query->orderByDesc('likes_count');
        }

        $posts = $query->limit(18)->get()->map(fn ($p) => [
            'id' => $p->id,
            'image' => $p->image,
            'isLocked' => $p->is_locked,
            'isVideo' => $p->is_video,
            'likes' => $this->formatNumber($p->likes_count),
            'caption' => $p->caption,
            'creator' => ['id' => $p->user->id],
        ]);

        return response()->json(['posts' => $posts]);
    }

    public function bookmarks()
    {
        $user = Auth::user();

        $posts = $user->bookmarks()
            ->with('post.user')
            ->latest()
            ->get()
            ->map(function ($bm) {
                $p = $bm->post;
                if (!$p) return null;
                return [
                    'id' => $p->id,
                    'image' => $p->image,
                    'isLocked' => $p->is_locked,
                    'isVideo' => $p->is_video,
                    'likes' => $p->likes_count,
                ];
            })->filter()->values();

        return response()->json(['posts' => $posts]);
    }

    private function formatNumber(int $num): string
    {
        if ($num >= 1000000) return round($num / 1000000, 1) . 'M';
        if ($num >= 1000) return round($num / 1000, 1) . 'K';
        return (string) $num;
    }
}
