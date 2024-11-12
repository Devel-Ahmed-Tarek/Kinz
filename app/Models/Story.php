<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'type', 'content', 'background_color', 'music_id',
        'video_duration', 'visibility', 'expires_at'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function likes() {
        return $this->hasMany(StoryLike::class);
    }

    public function replies() {
        return $this->hasMany(StoryReply::class);
    }

    public function reports() {
        return $this->hasMany(StoryReport::class);
    }
}