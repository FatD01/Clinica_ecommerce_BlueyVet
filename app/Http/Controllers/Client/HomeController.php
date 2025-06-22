<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
class HomeController extends Controller
{
    public function index()
    {
        $recentPosts = Post::where('type', 'blog')
                           ->where('is_published', true)
                           ->orderBy('published_at', 'desc')
                           ->limit(3)
                           ->get();
        return view('client.welcome', compact('recentPosts'));
    }
}
