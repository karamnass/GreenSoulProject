<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;

class Tip extends Model
{
    protected $fillable = [
        'title',
        'content',
        'image', // منخزن المسار هون
    ];

    //عند قراءة حقل image من الموديل، أرجعه كرابط URL كامل.
    public function getImageAttribute($value): ?string
    {
        if (! $value) {
            return null;
        }

        $disk = Storage::disk('public');

        return $disk->url($value);
    }

    // مسار الصورة الخام في قاعدة البيانات (بدون URL)
    // مفيد عند حذف الصورة من الـ Storage
    public function getImagePathAttribute(): ?string
    {
        return $this->attributes['image'] ?? null;
    }
}
