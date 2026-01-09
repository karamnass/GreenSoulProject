<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plant extends Model
{
    use HasFactory;

    protected $fillable = [
       'user_id',
        'plant_reference_id',
        'custom_name',
        'image',
        'watering_frequency_days',
        'next_watering_date',
        'notes',
    ];

    protected $casts = [
       'next_watering_date' => 'datetime',
        'watering_frequency_days' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reference()
    {
        return $this->belongsTo(PlantReference::class, 'plant_reference_id');
    }

    public function logs()
    {
        return $this->hasMany(PlantLog::class);
    }

    public function aiResults()
    {
        return $this->hasMany(AiResult::class);
    }

    public function plantReference()
    {
        return $this->belongsTo(PlantReference::class);
    }

    public function latestAiResult()
    {
        return $this->hasOne(AiResult::class)->latestOfMany();
    }

}
