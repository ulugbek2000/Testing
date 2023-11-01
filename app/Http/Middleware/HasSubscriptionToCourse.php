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
        if (Auth::check() && Auth::user()->isSubscribed($course)) {
            // Пользователь имеет подписку, позвольте доступ к полным урокам
            return $next($request);
        } else {
            return $this->showContentAsText($request, $next);
        }
        if ($request->routeIs('courseTopics'))
            $course = $request->course;
        if ($request->routeIs('topicLessons'))
            $course = $request->topic->course;
        if ($request->routeIs('lesson'))
            $course = $request->lesson;
    }

    private function showContentAsText(Request $request, Closure $next)
    {
        $response = $next($request);

        $data = json_decode($response->content(), true);

        if (is_array($data)) {
            $filteredData = [];
            foreach ($data as $item) {
                if (is_array($item) && array_key_exists('content', $item)) {
                    // Преобразование контента в строку
                    $contentAsString = (string) $item['content'];
                    $item['content'] = $contentAsString;
                }
                $filteredData[] = $item;
            }

            return response()->json($filteredData);
        }

        return $response;
    }
}
