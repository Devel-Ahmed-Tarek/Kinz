<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentReport extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'comment_id', 'report_type_id', 'status'];

    // Define the relationship with the Comment model
    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship with the ReportType model
    public function reportType()
    {
        return $this->belongsTo(ReportType::class);
    }
}