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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            // لمين الإشعار؟
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            // عنوان الإشعار
            $table->string('title')->nullable();

            // محتوى الإشعار
            $table->text('body');

            // هل تمت قراءته؟
            $table->boolean('is_read')->default(false);

            // example: watering, ai_result, complaint_reply ...
            $table->string('type')->nullable();
            
            // وقت مجدول اختياري
            $table->timestamp('scheduled_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
