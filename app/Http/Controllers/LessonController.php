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
        $lessons = $topic->lessons;
        $user = Auth::user();
        $lessonData = [];

        if (Auth::check()) {
            $isAdmin = $user->hasRole(UserType::Admin);
            $isSubscribed = $user->isSubscribed();

            if ($isAdmin || $isSubscribed) {
                foreach ($lessons as $lesson) {
                    $mediaData = [];

                    if ($lesson->hasMedia('content')) {
                        $mediaData = DB::table('media')
                            ->where('model_type', '=', 'App\\Models\\Lesson')
                            ->where('model_id', '=', $lesson->id)
                            ->select('custom_properties')
                            ->get();
                    }
                }
                return response()->json(['data' => $lessons]);
            }
        }

        if ($lessons->isNotEmpty()) {
            foreach ($lessons as $lesson) {
                $firstLesson = $lessons->first();

                if ($lesson->hasMedia('content')) {
                    $mediaData = DB::table('media')
                        ->where('model_type', '=', 'App\\Models\\Lesson')
                        ->where('model_id', '=', $lesson->id)
                        ->select('id', 'custom_properties',)
                        ->get();
                }

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
            'content' => $request->input('type') === 'text' ? 'required|string' : 'required|file',
        ]);

        $lesson = Lesson::create([
            'topic_id' => $request->input('topic_id'),
            'name' => $request->input('name'),
            'type' => $request->input('type')
        ]);

        if ($request->type === 'text') {
            $lesson->content = $request->input('content');
        } elseif ($request->type == 'video' || $request->type == 'audio') {

            $media = $lesson->addMediaFromRequest('content')->toMediaCollection('content');

            $getID3 = new getID3();
            $fileInfo = $getID3->analyze($media->getPath());

            $durationInSeconds = $fileInfo['playtime_seconds'];

            $media->setCustomProperty('duration', $durationInSeconds)->save();
            $videoPath = $media->getPath();
            $storagePath = substr($videoPath, strpos($videoPath, '/storage'));
            $lesson->content = $storagePath;
            // $lesson->duration = $media->custom_properties;
            $lesson->save();
        }

        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('cover', 'public');
            $lesson->cover = Storage::url($coverPath);
        }

        $lesson->save();

        // Обновляем информацию в таблице курсов
        if ($lesson->topic && $lesson->topic->course) {
            $course = $lesson->topic->course;
            $course->quantity_lessons = $course->lessons->count();

            // Добавляем длительность урока к общей длительности курса

            $course->hours_lessons = $course->lessons()
                ->leftJoin('media', function ($join) {
                    $join->on('media.model_id', '=', 'lessons.id')
                        ->where('media.model_type', '=', Lesson::class);
                })
                ->where('topics.course_id', $course->id)
                ->selectRaw('SUM(CASE WHEN media.custom_properties IS NOT NULL THEN JSON_UNQUOTE(JSON_EXTRACT(media.custom_properties, "$.duration")) ELSE 0 END) as total_duration')
                ->value('total_duration');
            $course->save();
        }

        return response()->json(['message' => 'Урок успешно создан.']);
    }



    /**
     * Display the specified resource.
     */
    public function show(Lesson $lesson)
    {
        // dd();
        if (Auth::check() && Auth::user()->isSubscribed($lesson->topic->course) or UserType::Admin) {
            return response()->json([
                'id' => $lesson->id,
                'name' => $lesson->name,
                'content' => $lesson->content,
                'duration' => $lesson->duration,
                'cover' => $lesson->cover,
                'type' => $lesson->type,
                'created_at' => $lesson->created_at,
                'updated_at' => $lesson->updated_at,
                'deleted_at' => $lesson->deleted_at,
            ], 200);
        }

        if (!Auth::check() || Auth::check() &&  $lesson->topic->course->isFirstLesson($lesson)) {

            $data[] = [
                'id' => $lesson->id,
                'name' => $lesson->name,
                'content' => $lesson->content,
                'duration' => $lesson->duration,
                'cover' => $lesson->cover,
                'type' => $lesson->type,
                'created_at' => $lesson->created_at,
                'updated_at' => $lesson->updated_at,
                'deleted_at' => $lesson->deleted_at,
            ];
            return response()->json(['data' => $data]);
        }

        return abort(403);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lesson $lesson)
    {
        $request->validate([
            'topic_id' => 'integer',
            'name' => 'string',
            'cover' => ['nullable', new FileOrString],
            'duration' => 'string|nullable',
            'content' =>  ['nullable', new FileOrString],
        ]);

        $coverPath = $lesson->cover;
        $contentPath = $lesson->content; // По умолчанию сохраняем текущий путь к контенту

        if ($request->hasFile('cover')) {
            // Удаляем старый файл обложки, если он существует
            if ($lesson->cover) {
                Storage::delete($lesson->cover);
            }
            // Загружаем и сохраняем новый файл обложки
            $coverPath = $request->file('cover')->store('cover', 'public');
        }

        if ($request->type === 'video' || $request->type === 'audio') {
            if ($request->hasFile('content')) {
                // Удаляем старые медиафайлы, если они существуют
                $lesson->clearMediaCollection('content');

                // Загружаем и сохраняем новый контентный файл в медиабиблиотеку
                $media = $lesson->addMediaFromRequest('content')->toMediaCollection('content');

                $getID3 = new getID3();
                $fileInfo = $getID3->analyze($media->getPath());

                $durationInSeconds = $fileInfo['playtime_seconds'];

                // Устанавливаем пользовательское свойство на медиафайле
                $media->setCustomProperty('duration', $durationInSeconds)->save();
                $videoPath = $media->getPath();
                $storagePath = substr($videoPath, strpos($videoPath, '/storage'));
                $contentPath = $lesson->content = $storagePath;
            }
        } elseif ($request->type === 'text') {
            // Если тип урока текстовый, сохраняем текстовое содержимое

            $contentPath = $lesson->content = $request->input('content');
            $lesson->save();
            // Обнуляем путь к контенту, так как его нет
        }

        $data = array_merge($request->only(['name', 'type', 'topic_id', 'duration']), [
            'cover' => $coverPath,
            'content' => $contentPath,
        ]);

        if ($lesson->topic && $lesson->topic->course) {
            $course = $lesson->topic->course;
            $course->quantity_lessons = $course->lessons->count();

            // Добавляем длительность уроков к общей длительности курса
            $course->hours_lessons = $course->lessons()
                ->leftJoin('media', function ($join) {
                    $join->on('media.model_id', '=', 'lessons.id')
                        ->where('media.model_type', '=', Lesson::class);
                })
                ->where('topics.course_id', $course->id)
                ->selectRaw('SUM(CASE WHEN media.custom_properties IS NOT NULL THEN JSON_UNQUOTE(JSON_EXTRACT(media.custom_properties, "$.duration")) ELSE 0 END) as total_duration')
                ->value('total_duration');

            $course->save();

            $lesson->update($data);

            return response()->json(['message' => 'Урок успешно обновлен.']);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lesson $lesson)
    {
        $lesson->delete();
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
            ->exists();
    
        if (!$existingDislike) {
            // Если пользователь еще не дизлайкал урок, увеличиваем количество дизлайков и создаем запись об этом действии
            $lesson->increment('dislikes');
            LessonUser::create([
                'lesson_id' => $lesson->id,
                'user_id' => $userId,
                'dislikes' => 1,
            ]);
            return response()->json(['message' => 'Lesson disliked successfully']);
        }
    
        return response()->json(['message' => 'You have already disliked this lesson']);
    }
    

    public function viewLesson(Request $request, Lesson $lesson)
    {
        $userId = $request->user()->id;

        // Проверяем, просматривал ли пользователь уже этот урок
        $existingAction = LessonUser::where('lesson_id', $lesson->id)
            ->where('user_id', $userId)
            ->exists();

        if (!$existingAction) {
            // Если пользователь еще не просматривал урок, увеличиваем количество просмотров и создаем запись об этом действии
            $lesson->increment('views');
            LessonUser::create([
                'lesson_id' => $lesson->id,
                'user_id' => $userId,
                'views' => 1,
            ]);
            return response()->json(['message' => 'Lesson viewed successfully']);
        }

        return response()->json(['message' => 'You have already viewed this lesson']);
    }
}
