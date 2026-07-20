<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostListResource;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /** GET /api/v1/posts?category=&per_page=&page= */
    public function index(Request $request)
    {
        $posts = Post::published()
            ->with('category')
            ->when($request->query('category'), function ($query, $slug) {
                $query->whereHas('category', fn ($q) => $q->where('slug', $slug));
            })
            ->orderByDesc('published_at')
            ->paginate(min($request->integer('per_page', 9), 50));

        return PostListResource::collection($posts);
    }

    /** GET /api/v1/posts/{slug} */
    public function show(string $slug)
    {
        $post = Post::published()
            ->with(['category', 'author'])
            ->where('slug', $slug)
            ->firstOrFail();

        return new PostResource($post);
    }
}
