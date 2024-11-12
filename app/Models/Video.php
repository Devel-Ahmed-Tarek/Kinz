<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'video_path',
        'likes',
        'views',
    ];

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function savedByUsers()
    {
        return $this->belongsToMany(User::class, 'saved_videos');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function interactions()
    {
        return $this->hasMany(Interaction::class);
    }

    public function likes()
    {
        return $this->hasMany(likes::class, 'video_id'); // Adjust the foreign key if necessary
    }

    // إضافة العلاقة مع الهاشتاجات
    public function hashtags()
    {
        return $this->hasMany(Hashtag::class);
    }
}
