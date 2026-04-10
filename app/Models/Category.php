<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'icon',
        'type',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}