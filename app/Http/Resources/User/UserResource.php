<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        // Ban details
        $ban = $this->ban;
        $isBanned = $ban !== null;
        $banType = $isBanned ? $ban->ban_type : null;
        $unbannedAt = $isBanned && $banType === 'temporary' ? $ban->unbanned_at : null;

        // Calculate total likes across all videos
        $totalLikes = $this->videos->sum('likes');

        return [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->name,
            'profile_image' => $this->profile_image,
            'country_code' => $this->country_code ?? 'N/A', // Default value if null
            'type' => $this->type,
            'email' => $this->email,
            'phone_number' => $this->phone_number, // Only show phone_number for admin users

            // Ban details
            'is_banned' => $isBanned,
            'ban_type' => $banType,
            'unbanned_at' => $unbannedAt?\Carbon\Carbon::parse($unbannedAt)->toDateTimeString() : null, // Format unbanned_at

            // Additional fields
            'videos_count' => $this->videos->count(), // Count of videos
            'followers_count' => $this->followers->count(), // Count of followers
            'following_count' => $this->following->count(), // Count of following
            'total_likes' => $totalLikes, // Total likes on user's videos
        ];
    }
}
