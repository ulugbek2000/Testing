<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class HasSubscriptionToCourse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if($request->routeIs('courseTopics')) 
            $course = $request->course;
        if($request->routeIs('topicLessons')) 
            $course = $request->topic->course;
        if($request->routeIs('lesson')) 
            $course = $request->lesson->topic->course;
        
            dd($course->id);

        if( Auth::check() && Auth::user()->isSubscribed($course) )
            return $next($request);
        else abort(403);
    }
}
