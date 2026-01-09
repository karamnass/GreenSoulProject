<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
  protected $fillable = [
    'user_id',
    'title',
    'body',
    'is_read',
    'type',
    'scheduled_at',
  ];

  //علاقة الإشعار مع المستخدم
  public function user()
  {
    return $this->belongsTo(User::class);
  }

  //Attribute casting
  protected $casts = [
    'is_read'      => 'boolean',
    'scheduled_at' => 'datetime',
  ];
}
