<?php

namespace App\Http\Controllers;


use App\Http\Requests\Auth\SignupRequestOtpRequest;
use App\Http\Requests\Auth\SignupVerifyRequest;
use App\Http\Requests\Auth\LoginRequestOtpRequest;
use App\Http\Requests\Auth\LoginVerifyRequest;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserDevice;
use App\Services\OtpService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Role;


class AuthController extends Controller
{
    public function signupRequestOtp(SignupRequestOtpRequest $request)
    {

        $phone = $request->phone;

        // هل المستخدم موجود من قبل ؟
        $user = User::where('phone', $phone)->first();


        if ($user) {
            return $this->error('This number is already registered. Use login.', null, 409);
        }

        // إنشاء المستخدم
        $user = User::create([
            'phone'     => $phone,
            'is_active' => true,
            // بس اختياري name وقت التسجيل وتخزنه
            'name'      => $request->name ?? null,
        ]);

        // إنشاء كود OTP من نوع signup باستخدام الخدمة
        $otp = $this->otpService->generate($user, 'signup');

        // في التطوير نرجّع الكود، في الإنتاج فقط رقم الهاتف
        $data = app()->environment('production')
            ? ['phone' => $phone]
            : ['phone' => $phone, 'otp_code' => $otp->code];

        return $this->success('The verification code has been sent.', $data);
    }

    public function signupVerify(SignupVerifyRequest $request)
    {
        $phone = $request->phone;
        $code  = $request->code;

        $user = User::where('phone', $phone)->first();

        if (! $user) {
            return $this->error('User not found.', null, 404);
        }

        // التحقق من الـ OTP (خاص بهذا المستخدم ونوع signup)
        $otp = $this->otpService->validate($user, $code, 'signup');

        if (! $otp) {
            return $this->error('Invalid or expired code.', null, 400);
        }

        // تعليم الـ OTP على أنه مستخدم
        $this->otpService->markUsed($otp, $user);

        // جلب role الافتراضي "user"
        $userRole = Role::where('role_name', 'user')->first();

        // تعيين role_id لأول مرة فقط لو موجود role
        if ($userRole && ! $user->role_id) {
            $user->role_id = $userRole->id;
        }

        $user->save();

        // تحديث بيانات المستخدم
        $user->name = $request->name ?? $user->name;
        $user->phone_verified_at = now();

        // تأكيد الهاتف وتحديث الاسم إن أحببت
        $user->update([
            'name'              => $request->name ?? $user->name,
            'phone_verified_at' => now(),
        ]);

        // إنشاء توكن عبر Sanctum (نستخدم نفس الاسم المستخدم في login)
        $token = $user->createToken('auth_token')->plainTextToken;

        $user->load('role');

        return $this->success([
            'user'  => new UserResource($user),
            'token' => $token,
        ], 'The account has been successfully created.');
    }

    public function loginRequestOtp(LoginRequestOtpRequest $request)
    {
        $phone = $request->phone;

        $user = User::where('phone', $phone)->first();

        if (! $user) {
            return $this->error('This phone is not registered.', null, 404);
        }

        if (! $user->is_active) {
            return $this->error('This account is inactive.', null, 403);
        }

        // إنشاء OTP جديد لهذا المستخدم (نوعه login)
        $otp = $this->otpService->generate($user, 'login');

        // في التطوير نرجّع الكود، في الإنتاج فقط رقم الهاتف
        $data = app()->environment('production')
            ? ['phone' => $phone]
            : ['phone' => $phone, 'otp_code' => $otp->code];

        return $this->success('OTP sent successfully.', $data);
    }

    public function loginVerify(LoginVerifyRequest $request)
    {
        $phone = $request->phone;
        $code  = $request->code;

        // إيجاد المستخدم من رقم الهاتف
        $user = User::where('phone', $phone)->first();

        if (! $user) {
            return $this->error('This phone is not registered.', null, 404);
        }

        if (! $user->is_active) {
            return $this->error('This account is inactive.', null, 403);
        }

        // التحقق من الـ OTP (خاص بهذا المستخدم ونوع login)
        $otp = $this->otpService->validate($user, $code, 'login');

        if (! $otp) {
            return $this->error('Invalid or expired OTP.', null, 422);
        }

        // تعليم الـ OTP أنه مستخدم
        $this->otpService->markUsed($otp, $user);

        // تخزين/تحديث معلومات الجهاز لو وصلت
        if ($request->device_token) {
            UserDevice::updateOrCreate(
                [
                    'user_id'      => $user->id,
                    'device_token' => $request->device_token,
                ],
                [
                    'platform'  => $request->platform ?? 'other',
                    'last_seen' => now(),
                ]
            );
        }

        // إنشاء توكن Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        $user->load('role');

        return $this->success([
            'user'  => new UserResource($user),
            'token' => $token,
        ], 'Login verified successfully.');
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        // حذف التوكن الحالي فقط
        $user->currentAccessToken()?->delete();

        return $this->success('Logged out successfully.');
    }

    public function resendOtp(ResendOtpRequest $request)
    {
        $phone = $request->phone;

        $user = User::where('phone', $phone)->first();

        if (! $user) {
            return $this->error('This phone is not registered.', null, 404);
        }

        if (! $user->is_active) {
            return $this->error('This account is inactive.', null, 403);
        }

        // إنشاء OTP جديد (نوع login) وإبطال القديم
        $otp = $this->otpService->generate($user, 'login');

        $data = app()->environment('production')
            ? ['phone' => $phone]
            : ['phone' => $phone, 'otp_code' => $otp->code];

        return $this->success('OTP resent successfully.', $data);
    }

    use ApiResponse;

    public function __construct(private OtpService $otpService) {}

    // ميثودات signupRequestOtp & signupVerify الحالية
    // اتركها كما هي الآن (سنرجع لها لاحقاً فقط لننقل الفاليديشن للـ Form Requests إن أحببنا)
}
