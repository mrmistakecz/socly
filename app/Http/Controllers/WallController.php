<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class WallController extends Controller
{
    public function index()
    {
        return Inertia::render('Wall');
    }

    public function posts()
    {
        // Return posts for API
        return response()->json([
            'posts' => []
        ]);
    }

    public function like($postId)
    {
        // Handle like
        return response()->json(['success' => true]);
    }

    public function comment(Request $request, $postId)
    {
        // Handle comment
        return response()->json(['success' => true]);
    }
}
