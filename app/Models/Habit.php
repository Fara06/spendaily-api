<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Habit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'habit_type',
        'title',
        'description',
        'score',
        'detected_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}