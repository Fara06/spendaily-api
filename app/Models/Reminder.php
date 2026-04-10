<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'remind_time',
        'frequency',
        'is_active'
    ];
}