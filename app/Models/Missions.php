<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    protected $fillable = [
        'title',
        'description',
        'type',
        'target_value',
        'duration',
        'reward_points',
        'color',
        'icon',
        'is_featured',
        'is_flash',
        'estimated_saving',
        'participants_count',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_flash' => 'boolean',
        'participants_count' => 'integer',
    ];

    public function userMissions()
    {
        return $this->hasMany(UserMission::class);
    }

    public function getParticipantsCount()
    {
        return $this->userMissions()->count();
    }

    public function isTrending()
    {
        return $this->getParticipantsCount() > 50;
    }

    public function isActive()
    {
        return true; 
    }

    public function formattedSaving()
    {
        if (!$this->estimated_saving) return null;

        return "Rp " . number_format($this->estimated_saving, 0, ',', '.');
    }
}
