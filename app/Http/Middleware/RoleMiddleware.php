<?php

namespace App\Http\Middleware;

use App\Enums\UserType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$args): Response
    {
        if($args == UserType::Student && !$request->user()->phoneVerified() || !$request->user()->emailVerified())
            return abort(403, 'Verification required');
        if($request->user()->hasAnyRole($args))
            return $next($request);
        else return abort(401);
    }
}
