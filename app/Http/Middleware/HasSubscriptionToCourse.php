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
            $courseId = $request->course;
        if($request->routeIs('topicLessons')) 
            $courseId = $request->topic;
        if($request->routeIs('lesson')) 
            $courseId = $request->lesson;
        
            dd($courseId);
            
        if( Auth::check() && Auth::user()->isSubscribed($request->get('course')) )
            return $next($request);
        else abort(403);
    }
}
