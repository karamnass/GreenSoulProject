<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class UserDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'platform',
        'device_name',
        'device_token',
        'last_login_at',
        'last_ip',
        'is_active',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
