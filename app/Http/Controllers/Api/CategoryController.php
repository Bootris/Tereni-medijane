<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    /** GET /api/v1/categories — with published-post counts. */
    public function index()
    {
        $categories = Category::withCount(['posts' => fn ($q) => $q->published()])
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }
}
