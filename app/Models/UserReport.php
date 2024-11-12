<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReport extends Model
{
    use HasFactory;
    protected $fillable = ['reporter_id', 'reported_id', 'report_type_id', 'status'];

    // العلاقة مع المستخدم المقدم للبلاغ
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    // العلاقة مع المستخدم الذي تم البلاغ عنه
    public function reported()
    {
        return $this->belongsTo(User::class, 'reported_id');
    }

    // العلاقة مع نوع البلاغ
    public function reportType()
    {
        return $this->belongsTo(ReportType::class);
    }
}
