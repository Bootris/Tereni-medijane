<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetAdminLocale
{
    /**
     * Filament ships Serbian as sr_Latn / sr_Cyrl, while the site uses "sr" —
     * map it so the admin UI renders translated instead of raw keys.
     */
    public function handle(Request $request, Closure $next)
    {
        if (app()->getLocale() === 'sr') {
            app()->setLocale('sr_Latn');
        }

        return $next($request);
    }
}
