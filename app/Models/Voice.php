<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voice extends Model
{
    use HasFactory;

    // Add the fields that can be mass assigned
    protected $fillable = [
        'name', // Name of the voice record
        'description', // Description field
        'voice', // The path to the uploaded audio file
        'image', // The path to the uploaded image file
    ];
}
