<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\TeamMember;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        $latestPosts = Cache::remember('site_latest_posts', 600, function () {
            return Post::published()
                ->with('category')
                ->orderByDesc('published_at')
                ->take(3)
                ->get();
        });

        $team = TeamMember::visible()->get();

        return view('home', compact('latestPosts', 'team'));
    }
}
