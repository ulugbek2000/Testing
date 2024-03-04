<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_title' => $this->lesson->topic->course->name,
            'topic_title' => $this->lesson->topic->topic_name,
            'lesson_title' => $this->lesson->name,
            'content' => $this->content,
            'is_hidden' => $this->is_hidden,
            'created_at' => $this->created_at,
            'user_name' => $this->user->name,
            'user_surname' => $this->user->surname,
            'user_photo' => $this->user->photo,
        ];
    }
}
