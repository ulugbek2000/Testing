<?php

namespace App\Http\Controllers;

use App\Enums\LessonType;
use App\Enums\LessonTypes;
use App\Enums\UserType;
use App\Models\Lesson;
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
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class LessonController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Topic $topic)
    {
        $lessons = $topic->lessons;

        if (Auth::check() && Auth::user()->isSubscribed($topic->course)) {
            return response()->json($lessons);
        }

        if ($topic->lessons->isNotEmpty()) {
            $data = [];

            $data = array_merge([$topic->lessons()->first()], $lessons->slice(1)->map(function ($v) {
                return $v->only(['id', 'name']);
            })->toArray());

            return response()->json($data);
        } else {
            return response()->json([]);
        }
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'topic_id' => 'integer',
            'name' => 'string',
            'cover' => 'image|file',
        ]);
    $lesson = new Lesson();
        $type = $request->input('type');
        $content = $request->input('content');
        $cover = $request->file('cover')->store('cover', 'public');
    
        $lesson->type = $type;
    
        $data = [
            'topic_id' => $request->has('topic_id') ? $request->topic_id : null,
            'name' => $request->name,
            'cover' => Storage::url($cover),
            'type' => $request->type,
        ];
    
        Lesson::create($data);
    
        $lesson->save();

        if ($type === 'text') {
            $lesson->content = $content;
        } elseif ($type === 'video' || $type === 'audio') {
            $media = $lesson->addMedia($request->file('content'))->toMediaCollection('content');
            $media->setAttribute('model_type', Lesson::class);
            $media->setAttribute('model_id', $lesson->id);
            $media->save();

            // Определение длительности видео
            $durationInSeconds = $media->getCustomProperty('duration');

            // Преобразование длительности в минуты
            $durationInMinutes = round($durationInSeconds / 60);

            // Сохранение длительности в модель урока
            $lesson->duration = $durationInMinutes;
        }

        $lesson->update([
            'content' => in_array($request->type, [LessonTypes::Video, LessonTypes::Audio]) ? $media->getUrl() : $request->content,
        ]);
        $lesson->save();

        return response()->json(['message' => 'Lesson created successfully.']);
    }



    /**
     * Display the specified resource.
     */
    public function show(Lesson $lesson)
    {
        // dd();
        if (Auth::check() && Auth::user()->isSubscribed($lesson->topic->course)) {
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
            'cover' => 'nullable|file',
            'duration' => 'string|nullable',
            'content' => 'nullable',
        ]);

        $coverpath = $lesson->cover;
        $content = $lesson->content;

        if ($request->hasFile('cover')) {
            // Delete old cover file if needed
            Storage::delete($lesson->cover);
            // Upload and store new cover file
            $coverpath = $request->file('cover')->store('cover', 'public');
        }
        if ($request->type == LessonTypes::Video ||  $request->type == LessonTypes::Audio) {
            if ($request->hasFile('content')) {
                // Delete old content file if needed
                Storage::delete($lesson->content);
                // Upload and store new content file
                $content = $request->file('content')->store('content');
            }
        } else {
            $content = $request->content;
        }

        $data = array_merge($request->only(['name', 'type', 'topic_id', 'duration']), [
            'cover' => $coverpath,
            'content' => $content
        ]);

        $lesson->update($data);

        // return response()->json($data);

        return response()->json(['message' => 'Lesson updated successfully']);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lesson $lesson)
    {
        $lesson->delete();
        return response()->json([
            'message' => "Lesson succefully deleted."
        ], 200);
    }
}
