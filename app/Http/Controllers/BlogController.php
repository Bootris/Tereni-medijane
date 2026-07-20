<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::query()
            ->whereHas('posts', fn ($query) => $query->published())
            ->withCount(['posts' => fn ($query) => $query->published()])
            ->orderBy('name')
            ->get();

        $activeCategory = $request->query('category');

        $posts = Post::published()
            ->with('category')
            ->when($activeCategory, fn ($query) => $query->whereHas(
                'category', fn ($q) => $q->where('slug', $activeCategory),
            ))
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('blog.index', compact('posts', 'categories', 'activeCategory'));
    }

    public function show(string $slug)
    {
        $post = Post::published()
            ->with('category')
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Post::published()
            ->where('id', '!=', $post->id)
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        return view('blog.show', compact('post', 'related'));
    }
}
