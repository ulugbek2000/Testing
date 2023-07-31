<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LocaleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->get('lang', 'en'); // Предполагается, что "lang" - это имя параметра, в котором передается язык
        app()->setLocale($locale);

        return $next($request);
    }
}