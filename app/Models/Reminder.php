<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Reminder extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'remind_time',
        'frequency',
        'is_active',
        'last_sent_at'
    ];

    protected $casts = [
        'last_sent_at' => 'datetime', 
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
