<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Support\Facades\Route;

/*
| Public read-only content API. This is the stable contract every frontend
| (Astro / Next / Vue / Blade) consumes. Changing a response shape means a new
| /v2 group — /v1 stays. See docs/BACKEND.md.
*/
Route::prefix('v1')->group(function () {
    Route::get('settings', [SettingsController::class, 'show']);

    Route::get('posts', [PostController::class, 'index']);
    Route::get('posts/{slug}', [PostController::class, 'show']);

    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('team', [TeamController::class, 'index']);

    Route::post('contact', [ContactController::class, 'store'])
        ->middleware('throttle:5,1');
});
