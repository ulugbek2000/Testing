<?php

namespace App\Http\Controllers;

use App\Enums\LessonTypes;
use App\Enums\UserType;
use App\Models\Lesson;
use App\Models\LessonUser;
use App\Models\Media as ModelsMedia;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Enum;
use Symfony\Contracts\Service\Attribute\Required;
use Nette\Utils\Random;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use FFMpeg\FFProbe;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;
use getID3;
use Illuminate\Support\Facades\Log;
use App\Models\Media;
use App\Rules\FileOrString;
use Illuminate\Support\Facades\DB;

class LessonController extends Controller
{
    public function index(Topic $topic)
    {
        $lessons = $topic->lessons()->orderBy('order')->get();
        $user = Auth::user();

        if (Auth::check()) {
            $isAdmin = $user->hasRole(UserType::Admin);
            $isSubscribed = $user->isSubscribed($topic->course);

            if ($isAdmin || $isSubscribed) {
                // $completedLessonIds = $user->completedLessons()->pluck('lesson_id')->toArray();
                // $availableLessons = $topic->lessons()->whereNotIn('id', $completedLessonIds)->orderBy('order')->get();

                return response()->json(['data' => $lessons]);
            }
        }

        if ($lessons->isNotEmpty()) {
            $firstLesson = $lessons->first();

            // Для остальных уроков показываем частичную информацию
            $otherLessons = $lessons->slice(1)->map(function ($lesson) {
                return [
                    'id' => $lesson->id,
                    'name' => $lesson->name,
                ];
            });

            return response()->json(['data' => array_merge([$firstLesson], $otherLessons->toArray())]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $request->validate([
            'topic_id' => 'nullable|integer',
            'name' => 'nullable|string',
            'cover' => 'image|file',
            'duration' => 'nullable',
            'type' => 'required|in:text,video,audio',
            'content' => 'nullable|url|string',
            'duration' => 'nullable|string',
            'file_name' => 'nullable|string',
        ]);

        $lesson = Lesson::create([
            'topic_id' => $request->input('topic_id'),
            'name' => $request->input('name'),
            'type' => $request->input('type'),
            'duration' => $request->input('duration'),
            'file_name' => $request->input('file_name'),
        ]);

        if ($request->type === 'text') {
            $lesson->content = $request->input('content');
        } elseif ($request->type == 'video' || $request->type == 'audio') {
            $lesson->content = $request->input('content');
        }

        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('cover', 'public');
            $lesson->cover = Storage::url($coverPath);
        }

        $lesson->save();

        // Обновляем информацию в таблице курсов
        if ($lesson->topic && $lesson->topic->course) {
            $course = $lesson->topic->course;

            $quantity_lessons = 0;
            $total_duration = 0;

            // Перебираем все темы курса
            foreach ($course->topics as $topic) {
                // Увеличиваем количество уроков на количество уроков в текущей теме
                $quantity_lessons += $topic->lessons()->count();

                // Считаем общую продолжительность уроков в текущей теме
                $total_duration += $topic->lessons()
                    ->where('topic_id', $topic->id)
                    ->selectRaw('SUM(CASE WHEN duration IS NOT NULL THEN duration ELSE 0 END) as total_duration')
                    ->value('total_duration');
            }


            // Присваиваем значения
            $course->quantity_lessons = $quantity_lessons;
            $course->hours_lessons = $total_duration;

            // Сохраняем изменения
            $course->save();
        }
        return response()->json(['message' => 'Урок успешно создан.']);
    }



    /**
     * Display the specified resource.
     */
    public function show(Lesson $lesson)
    {
        $user = Auth::user();
        if (Auth::check() && ($user->isSubscribed($lesson->topic->course) || Auth::user()->hasRole(UserType::Admin))) {
            $completedLessonIds = $user->completedLessons()->pluck('lesson_id')->toArray();

            // Проверяем, завершен ли предыдущий урок
            $currentLessonOrder = $lesson->order;
            $previousLessonOrder = max(1, $currentLessonOrder - 1);
            $previousLessonCompleted = in_array($previousLessonOrder, $completedLessonIds);

            // Если предыдущий урок завершен или это первый урок в курсе
            if ($previousLessonCompleted || $lesson->topic->course->isFirstLesson($lesson)) {
                return response()->json([
                    'id' => $lesson->id,
                    'name' => $lesson->name,
                    'content' => $lesson->content,
                    'duration' => $lesson->duration,
                    'cover' => $lesson->cover,
                    'type' => $lesson->type,
                    'file_name' => $lesson->file_name,
                    'duration' => $lesson->duration,
                    'created_at' => $lesson->created_at,
                    'updated_at' => $lesson->updated_at,
                    'deleted_at' => $lesson->deleted_at,
                ], 200);
            } else if (!Auth::check() || Auth::check() &&  $lesson->topic->course->isFirstLesson($lesson)) {

                $data[] = [
                    'id' => $lesson->id,
                    'name' => $lesson->name,
                    'content' => $lesson->content,
                    'duration' => $lesson->duration,
                    'cover' => $lesson->cover,
                    'type' => $lesson->type,
                    'file_name' => $lesson->file_name,
                    'duration' => $lesson->duration,
                    'created_at' => $lesson->created_at,
                    'updated_at' => $lesson->updated_at,
                    'deleted_at' => $lesson->deleted_at,
                ];
                return response()->json(['data' => $data]);
            }

            return abort(403);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lesson $lesson)
    {
        $request->validate([
            'topic_id' => 'nullable|integer',
            'name' => 'nullable|string',
            'cover' => 'nullable|image|file',
            'duration' => 'nullable',
            'type' => 'required|in:text,video,audio',
            'content' => 'nullable|url|string',
            'duration' => 'nullable|string',
            'file_name' => 'nullable|string',
        ]);

        if ($request->has('type') && $request->type !== $lesson->type) {
            if ($lesson->type === 'video' || $lesson->type === 'audio') {
                $lesson->clearMediaCollection('content');
            }
        }

        $data = [
            'topic_id' => $request->input('topic_id', $lesson->topic_id),
            'name' => $request->input('name', $lesson->name),
            'type' => $request->input('type', $lesson->type),
            'content' => $request->input('content', $lesson->content),
            'duration' => $request->input('duration', $lesson->duration),
            'file_name' => $request->input('file_name', $lesson->file_name),
        ];

        // Если загружена новая обложка
        if ($request->hasFile('cover')) {
            // Удаляем старую обложку, если она существует
            if ($lesson->cover) {
                Storage::delete($lesson->cover);
            }
            // Сохраняем новую обложку
            $coverPath = $request->file('cover')->store('cover', 'public');
            $data['cover'] = Storage::url($coverPath);
        }

        $lesson->update($data);

        // Обновляем информацию в таблице курсов
        if ($lesson->topic && $lesson->topic->course) {
            $course = $lesson->topic->course;

            $quantity_lessons = 0;
            $total_duration = 0;

            // Перебираем все темы курса
            foreach ($course->topics as $topic) {
                // Увеличиваем количество уроков на количество уроков в текущей теме
                $quantity_lessons += $topic->lessons()->count();

                // Считаем общую продолжительность уроков в текущей теме
                $total_duration += $topic->lessons()
                    ->where('topic_id', $topic->id)
                    ->selectRaw('SUM(CASE WHEN duration IS NOT NULL THEN duration ELSE 0 END) as total_duration')
                    ->value('total_duration');
            }

            // Присваиваем значения
            $course->quantity_lessons = $quantity_lessons;
            $course->hours_lessons = $total_duration;

            // Сохраняем изменения
            $course->save();
        }


        return response()->json(['message' => 'Урок успешно обновлен.']);
    }

    public function updateOrder(Request $request)
    {
        $data = $request->validate([
            'lesson_ids' => 'required|array',
            'lesson_ids.*' => 'required|integer',
        ]);

        foreach ($data['lesson_ids'] as $index => $lessonId) {
            $lesson = Lesson::findOrFail($lessonId);
            $lesson->update(['order' => $index + 1]);
        }

        Lesson::where('order', '>=', $index + 1)
            ->whereNotIn('id', $data['lesson_ids'])
            ->increment('order');

        return response()->json(['success' => true], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lesson $lesson)
    {
        $course = $lesson->topic->course;

        if (isset($lesson->duration)) {
            $durationToRemove = $lesson->duration;
        } else {
            $durationToRemove = 0;
        }

        $lesson->userLessonProgress()->delete();

        $lesson->delete();

        if ($course) {
            $course->update([
                'quantity_lessons' => $course->lessons()->count(),
                'hours_lessons' => max(
                    0,
                    $course->lessons()->where('topics.course_id', $course->id)->sum('duration') - $durationToRemove
                ),
            ]);
        }

        return response()->json([
            'message' => "Урок успешно удален."
        ], 200);
    }

    public function likeLesson(Request $request, Lesson $lesson)
    {
        $userId = $request->user()->id;

        // Проверяем, лайкал ли пользователь уже этот урок
        $existingLike = LessonUser::where('lesson_id', $lesson->id)
            ->where('user_id', $userId)
            ->first();

        if (!$existingLike) {
            // Если пользователь еще не лайкал урок, увеличиваем количество лайков и создаем запись об этом действии
            $lesson->increment('likes');
            LessonUser::create([
                'lesson_id' => $lesson->id,
                'user_id' => $userId,
                'likes' => 1,
            ]);
            return response()->json(['message' => 'Lesson liked successfully']);
        } elseif ($existingLike->likes == 1) {
            // Если пользователь уже лайкал урок, то отменяем его лайк и уменьшаем количество лайков
            $existingLike->delete();
            $lesson->decrement('likes');
            return response()->json(['message' => 'Lesson like cancelled successfully']);
        }

        return response()->json(['message' => 'You have already liked this lesson']);
    }

    public function dislikeLesson(Request $request, Lesson $lesson)
    {
        $userId = $request->user()->id;

        // Проверяем, дизлайкал ли пользователь уже этот урок
        $existingDislike = LessonUser::where('lesson_id', $lesson->id)
            ->where('user_id', $userId)
            ->first();

        if (!$existingDislike) {
            // Если пользователь еще не дизлайкал урок, увеличиваем количество дизлайков и создаем запись об этом действии
            $lesson->increment('dislikes');
            LessonUser::create([
                'lesson_id' => $lesson->id,
                'user_id' => $userId,
                'dislikes' => 1,
            ]);
            return response()->json(['message' => 'Lesson disliked successfully']);
        } elseif ($existingDislike->dislikes == 1) {
            // Если пользователь уже лайкал урок, то отменяем его лайк и уменьшаем количество лайков
            $existingDislike->delete();
            $lesson->decrement('dislikes');
            return response()->json(['message' => 'Lesson like cancelled successfully']);
        }

        return response()->json(['message' => 'You have already disliked this lesson']);
    }
}
