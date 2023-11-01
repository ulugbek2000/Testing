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
        $course = 0;
        if ($request->routeIs('courseTopics'))
            $course = $request->course;
        if ($request->routeIs('topicLessons'))
            $course = $request->topic->course;
        if ($request->routeIs('lesson'))
            $course = $request->lesson->topic->course;

        if (Auth::check() && Auth::user()->isSubscribed($course)) {
            dd(Auth::check() && Auth::user()->isSubscribed($course), $course);
                return $next($request);
            } else {
                return $this->showContentAsText($request, $next);
            }
        }
    
        private function showContentAsText(Request $request, Closure $next)
        {
            $response = $next($request);
        
            $data = json_decode($response->content(), true);
        
            if (is_array($data)) {
                $filteredData = [];
                foreach ($data as $item) {
                    if (is_array($item) && array_key_exists('name', $item)) {
                        // Преобразование контента в строку
                        $contentAsString = (string) $item['name'];
                        $item['name'] = $contentAsString;
                    }
                    $filteredData[] = $item;
                }
        
                return response()->json($filteredData);
            }
        
            return $response;
        }
        
}
