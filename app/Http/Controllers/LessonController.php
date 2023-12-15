<?php

namespace App\Http\Controllers;

use App\Enums\LessonTypes;
use App\Enums\UserType;
use App\Models\Lesson;
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

class LessonController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Topic $topic)
    {
        $lessons = $topic->lessons;
        $user = Auth::user();
    
        if (Auth::check()) {
            $isAdmin = $user->hasRole(UserType::Admin);
            $isSubscribed = $user->isSubscribed($topic->course);
    
            if ($isAdmin || $isSubscribed) {
                return response()->json(['data' => $lessons]);
            }
        }
    
        $data = [];
    
        if ($lessons->isNotEmpty()) {
            $firstLesson = $lessons->first();
    
            if ($firstLesson && $firstLesson->hasMedia('content')) {
                $media = $firstLesson->getFirstMedia('content');
                $mediaData = [
                    'id' => $media->id,
                    'custom_properties' => $media->custom_properties,
                    'original_url' => $media->original_url,
                ];
    
                $data = [
                    'id' => $firstLesson->id,
                    'name' => $firstLesson->name,
                    'topic_id' => $firstLesson->topic_id,
                    'cover' => $firstLesson->cover,
                    'content' => $firstLesson->content,
                    'type' => $firstLesson->type,
                    'media' => $mediaData,
                ];
    
                return response()->json(['data' => [$data]]);
            }
    
            $data[] = [
                'id' => $firstLesson->id,
                'name' => $firstLesson->name,
                'topic_id' => $firstLesson->topic_id,
                'cover' => $firstLesson->cover,
                'content' => $firstLesson->content,
                'type' => $firstLesson->type,
            ];
    
            // Добавляем информацию о других уроках
            foreach ($lessons->slice(1) as $lesson) {
                $media = $lesson->getFirstMedia('content');
                $duration = $media ? $media->getCustomProperty('duration') : null;
dd($duration);
                $data[] = [
                    'id' => $lesson->id,
                    'name' => $lesson->name,
                    'duration' => $duration,
                ];
            }
        }
    
        return response()->json(['data' => $data]);
    }
    

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $request->validate([
            'topic_id' => 'nullable|integer',
            'name' => 'string',
            'cover' => 'image|file',
            'duration' => 'nullable',
            'type' => 'required|in:text,video,audio',
            'content' => $request->input('type') === 'text' ? 'required|string' : 'required|file',
        ]);

        $lesson = Lesson::create([
            'topic_id' => $request->topic_id,
            'name' => $request->name,
            'type' => $request->type,
        ]);

        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('cover', 'public');
            $lesson->cover = Storage::url($coverPath);
        }

        if ($request->type === 'text') {
            $lesson->content = $request->input('content');
        } elseif ($request->type === 'video' || $request->type === 'audio') {
            $media = $lesson->addMediaFromRequest('content')->toMediaCollection('content');

            $ffmpeg = FFProbe::create([
                'ffmpeg.binaries' => '/home/softclub/domains/lmsapi.softclub.tj/ffmpeg-git-20231128-amd64-static/ffmpeg',
                'ffprobe.binaries' => '/home/softclub/domains/lmsapi.softclub.tj/ffmpeg-git-20231128-amd64-static/ffprobe'
            ]);

            $localPath = $media->getPath();
            $durationInSeconds = $ffmpeg->format($localPath)->get('duration');

            $media->setCustomProperty('duration', $durationInSeconds)->save();
            $lesson->content = $media->getUrl();
            $lesson->duration = $media->custom_properties;
        }

        $lesson->save();
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
            'cover' => 'nullable|file',
            'duration' => 'string|nullable',
            'content' => 'nullable',
        ]);

        $coverPath = $lesson->cover;
        $contentPath = $lesson->content;

        if ($request->hasFile('cover')) {
            // Delete old cover file if needed
            Storage::delete($lesson->cover);
            // Upload and store new cover file
            $coverPath = $request->file('cover')->store('cover', 'public');
        }

        if ($request->type == LessonTypes::Video || $request->type == LessonTypes::Audio) {
            if ($request->hasFile('content')) {
                // Delete old content file if needed
                Storage::delete($lesson->content);
                // Upload and store new content file
                $contentPath = $request->file('content')->store('content', 'public');

                // Get media associated with the lesson
                $media = $lesson->getMedia('content')->first();

                if ($media) {
                    // Calculate and update duration
                    $ffmpeg = FFProbe::create([
                        'ffmpeg.binaries' => '/home/softclub/domains/lmsapi.softclub.tj/ffmpeg-git-20231128-amd64-static/ffmpeg',
                        'ffprobe.binaries' => '/home/softclub/domains/lmsapi.softclub.tj/ffmpeg-git-20231128-amd64-static/ffprobe'
                    ]);

                    $localPath = storage_path("app/public/{$contentPath}");
                    $durationInSeconds = $ffmpeg->format($localPath)->get('duration');

                    // Set custom property on the media, not on the lesson
                    $media->setCustomProperty('duration', $durationInSeconds)->save();
                }
            }
        }

        $data = array_merge($request->only(['name', 'type', 'topic_id', 'duration']), [
            'cover' => $coverPath,
            'content' => $contentPath
        ]);

        $lesson->update($data);

        return response()->json(['message' => 'Урок успешно обновлен.']);
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
}
