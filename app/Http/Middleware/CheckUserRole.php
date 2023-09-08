<?php

namespace App\Http\Middleware;

use App\Enums\UserType;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $user = Auth::user();

        if (!$user->roles()->exists()) {

            return $next($request);
        }


      
      
      
      
      
      
      
      
      
        //  if ($request->user()->hasRole(UserType::Admin)) {
        //     return $next($request);
        // } else {
        //     // Обработка запрета доступа
        //     return response()->json(['error' => 'Access denied'], 403);
        // }

        // if ($request->user()->hasRole(UserType::Teacher)) {
        //     return $next($request);
        // } else {
        //     // Обработка запрета доступа
        //    return response()->json(['error' => 'Access denied'], 403);
        // }

        // if ($request->user()->hasRole(UserType::Student)) {
        //     return $next($request);
        // } else {
        //     // Обработка запрета доступа
        //     return response()->json(['error' => 'Access denied'], 403);
        // }
    }
}
