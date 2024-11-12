<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserStoryResource extends JsonResource
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
            'username' => $this->username,
            'name' => $this->name,
            'profile_image' => $this->profile_image,
            'stories' => $this->stories->map(function ($story) {
                return [
                    'id' => $story->id,
                    'type' => $story->type,
                    'content' => $story->content, // رابط أو مسار القصة (الصورة أو الفيديو)
                    'background_color' => $story->background_color,
                    'created_at' => $story->created_at->toDateTimeString(),
                    'expires_at' => $story->expires_at->toDateTimeString(),
                ];
            }),
        ];
    }
}
