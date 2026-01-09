<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlantReference extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'image',
    ];

      /**
     * إرجاع رابط URL عند قراءة image.
     */
    public function getImageAttribute($value): ?string
    {
        if (! $value) {
            return null;
        }

        return Storage::disk('public')->url($value);
    }

    /**
     * المسار الخام للصورة المخزّن في قاعدة البيانات (بدون URL).
     */
    public function getImagePathAttribute(): ?string
    {
        return $this->attributes['image'] ?? null;
    }

    /**
     * النباتات التي تستخدم هذه الـ reference (علاقة اختيارية).
     */
    public function plants()
    {
        return $this->hasMany(Plant::class);
    }
    
}
