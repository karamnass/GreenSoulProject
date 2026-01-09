<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'plant_id',
        // legacy
        'description',
        'image',
        'accuracy',
        'extra_data',

        // predict fields
        'plant_name',
        'confidence',
        'recommendation',
        'raw_response',
    ];

    protected $casts = [
        'accuracy' => 'decimal:2',
        'extra_data' => 'array',
        'confidence' => 'decimal:4',
        'raw_response' => 'array',
    ];

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }
}
