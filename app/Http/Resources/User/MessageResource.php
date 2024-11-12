<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'message_id' => $this->id,
            'chat_id' => $this->chat_id,
            'sender' => new UserResource($this->sender),
            'type' => $this->type,
            'message' => $this->message,
            'file_url' => $this->file_url,
            'created_at' => $this->created_at,
        ];
    }
}
