<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            // الربط مع المستخدم
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            // كود الـ OTP (مثلاً 6 أرقام)
            $table->string('code', 6);

            // نوع العملية: Signup / Login ...
            $table->string('type')->default('login'); // 'signup', 'login' ...

            // هل تم استخدام الكود؟
            $table->boolean('is_used')->default(false);

            // تاريخ انتهاء صلاحية الكود
            $table->timestamp('expires_at');

            $table->timestamps();

            $table->index(['user_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
