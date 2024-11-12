<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportVideo extends Model
{
    use HasFactory;

    protected $guarded = [];
    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship with the Video model
    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    // Define the relationship with the ReportType model
    public function reportType()
    {
        return $this->belongsTo(ReportType::class);
    }
}
