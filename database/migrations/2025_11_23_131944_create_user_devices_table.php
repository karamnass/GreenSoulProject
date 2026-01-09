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
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            // android / web (نلتزم بالقيمتين حسب ما قررنا)
            $table->enum('platform', ['android', 'web']);

            // اسم الجهاز (اختياري – من الـ app)
            $table->string('device_name')->nullable();

            // توكن الإشعارات (مثلاً FCM token)
            $table->text('device_token')->nullable();

            // آخر تسجيل دخول من هذا الجهاز
            $table->timestamp('last_login_at')->nullable();

            // آخر IP
            $table->string('last_ip', 45)->nullable();

            // تفعيل/تعطيل هذا الجهاز
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['user_id', 'platform']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
