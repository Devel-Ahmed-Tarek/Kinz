<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'chat_id' => $this->id,
            'user1' => new UserResource($this->user1),
            'user2' => new UserResource($this->user2),
            'last_message' => new MessageResource($this->messages()->latest()->first()),
        ];
    }
}
