<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingsTarget extends Model
{
    protected $fillable = [
        'user_id',
        'target_amount',
        'daily_limit',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}