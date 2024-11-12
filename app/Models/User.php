<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected function ProfileImage(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? asset('storage/' . $value) : 'https://ui-avatars.com/api/?name=' . $this->name
        );
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function followers()
    {
        return $this->hasMany(Follower::class, 'user_id');
    }

    public function following()
    {
        return $this->hasMany(Follower::class, 'follower_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function ban()
    {
        return $this->hasOne(Ban::class);
    }

    public function savedVideos()
    {
        return $this->belongsToMany(Video::class, 'saved_videos');
    }

    public function interactions()
    {
        return $this->hasMany(Interaction::class);
    }

    public function stories()
    {
        return $this->hasMany(Story::class);
    }

    public function pointsWallet()
    {
        return $this->hasOne(PointsWallet::class);
    }

    public function votesWallet()
    {
        return $this->hasOne(VotesWallet::class);
    }

}
