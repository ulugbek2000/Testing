<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $commentsQuery = Comment::with(['lesson.topic.course', 'user'])
            ->orderBy('created_at', 'desc');
            if (Auth::user()->hasRole(UserType::Admin)) {
                // Если пользователь администратор, не применяем условие is_hidden
            } else {
                // Если пользователь не администратор, выбираем только не скрытые комментарии
                $commentsQuery->where('is_hidden', false);
            }
        
            if ($request->has('name')) {
                $name = $request->input('name');
                $commentsQuery->whereHas('user', function ($query) use ($name) {
                    $query->where('name', 'like', "%$name%")
                          ->orWhere('surname', 'like', "%$name%");
                });
            }

        if ($request->has('date')) {
            $date = $request->input('date');
            $commentsQuery->whereDate('created_at', $date);
        }

        $per_page = $request->per_page ?? 12;
        $comments = $commentsQuery->paginate($per_page);

        return response()->json([
            'comments' => CommentResource::collection($comments),
            'meta' => [
                'current_page' => $comments->currentPage(),
                'from' => $comments->firstItem(),
                'last_page' => $comments->lastPage(),
                'links' => [
                    'previous' => $comments->previousPageUrl(),
                    'next' => $comments->nextPageUrl(),
                ],
                'path' => $comments->path(),
                'per_page' => $comments->perPage(),
                'to' => $comments->lastItem(),
                'total' => $comments->total()
            ]
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (Auth::check()) {

            $student = $user->hasRole(UserType::Student);
            $isSubscribed = $user->isSubscribed();

            if ($student && $isSubscribed) {

                $request->validate([
                    'lesson_id' => 'required',
                    'content' => 'required|string|max:256'
                ]);
                $comment = new Comment();
                $comment->lesson_id = $request->lesson_id;
                $comment->user_id = $request->user()->id;
                $comment->content = $request->content;
                $comment->save();
                return response()->json(['comment' => $comment]);
            }
        
        }
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();
        return response()->json(['msg' => 'Комментария успешно удален']);
    }

    public function hideComment(Comment $comment)
    {
        if (Auth::user()->hasRole(UserType::Admin)) {
            // Инвертируем значение поля is_hidden
            $comment->update(['is_hidden' => !$comment->is_hidden]);

            // Определяем текстовое сообщение в зависимости от нового состояния
            $message = $comment->is_hidden ? 'Комментарий успешно скрыт' : 'Комментарий успешно отображен';

            return response()->json(['message' => $message], 200);
        }
    }
}
