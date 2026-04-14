<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMission extends Model
{
    protected $table = 'user_missions';

    protected $fillable = [
        'user_id',
        'mission_id',
        'start_date',
        'end_date',
        'progress',
        'status',
        'is_claimed',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
        'is_claimed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isClaimed()
    {
        return $this->is_claimed === true;
    }

    public function progressPercentage()
    {
        if (!$this->mission || $this->mission->target_value == 0) {
            return 0;
        }

        return min(
            round(($this->progress / $this->mission->target_value) * 100, 2),
            100
        );
    }

    public function updateStatus()
    {
        if ($this->progress >= $this->mission->target_value) {
            $this->status = 'completed';
        }

        $this->save();
    }
}