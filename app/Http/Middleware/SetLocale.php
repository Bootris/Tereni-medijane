<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->route('locale');

        if (in_array($locale, ['en', 'sr'])) {
            App::setLocale($locale);
            Session::put('locale', $locale);
        }

        return $next($request);
    }
}
