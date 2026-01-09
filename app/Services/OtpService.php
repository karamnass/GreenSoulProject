<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\User;
use Carbon\Carbon;

class OtpService
{
   // //إنشاء OTP جديد للمستخدم (لنستخدمه في login)
   // public function generate(User $user, string $type = 'login'): OtpCode
   // {
   //     $code = random_int(100000, 999999);
//
   //     // مدة الصلاحية (حالياً 5 دقائق)
   //     $expiryMinutes = 5;
//
   //     // إبطال أي OTP قديم غير مستخدم لنفس المستخدم ولنفس النوع
   //     OtpCode::where('user_id', $user->id)
   //         ->where('type', $type)
   //         ->where('is_used', false)
   //         ->update(['is_used' => true]);
//
   //     return OtpCode::create([
   //         'user_id'    => $user->id,
   //         'code'       => $code,
   //         'type'       => $type, // login
   //         'is_used'    => false,
   //         'expires_at' => Carbon::now()->addMinutes($expiryMinutes),
   //     ]);
   // }
//
   // /**
   //  * التحقق من صحة الـ OTP لمستخدم معيّن.
   //  */
   // public function validate(User $user, string $code, string $type = 'login'): ?OtpCode
   // {
   //     return OtpCode::where('user_id', $user->id)
   //         ->where('code', $code)
   //         ->where('type', $type)
   //         ->where('is_used', false)
   //         ->where('expires_at', '>', now())
   //         ->first();
   // }
//
   // /**
   //  * تعليم الـ OTP على أنه مستخدم وربطه بالمستخدم إن لزم.
   //  */
   // public function markUsed(OtpCode $otp, ?User $user = null): void
   // {
   //     $otp->update([
   //         'is_used' => true,
   //         'user_id' => $user?->id ?? $otp->user_id,
   //     ]);
   // }

//إنشاء OTP جديد لمستخدم معيّن (signup أو login)
    public function generate(User $user, string $type = 'signup'): OtpCode
    {
        // حذف أي أكواد سابقة غير مستخدمة من نفس النوع لنفس المستخدم
        OtpCode::where('user_id', $user->id)
            ->where('type', $type)
            ->where('is_used', false)
            ->delete();

        $code = random_int(100000, 999999);

        return OtpCode::create([
            'user_id'    => $user->id,
            'code'       => $code,
            'type'       => $type,           // 'signup' أو 'login'
            'is_used'    => false,
            'expires_at' => now()->addMinutes(5),
        ]);
    }

    // التحقق من صحة الـ OTP (حسب المستخدم والنوع)
    public function validate(User $user, string $code, string $type): ?OtpCode
    {
        return OtpCode::where('user_id', $user->id)
            ->where('code', $code)
            ->where('type', $type)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();
    }

    //تعليم الـ OTP على أنه مستخدم وربطه بالمستخدم إن لزم
    public function markUsed(OtpCode $otp, ?User $user = null): void
    {
        $otp->update([
            'is_used' => true,
            'user_id' => $user?->id ?? $otp->user_id,
        ]);
    }
}
