<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoryReport extends Model
{
    use HasFactory;
    protected $fillable = ['story_id', 'user_id', 'report_type_id'];

    public function story()
    {
        return $this->belongsTo(Story::class);
    }

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
