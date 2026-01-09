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
        Schema::create('plants', function (Blueprint $table) {
            $table->id();

            // النبتة ملك لمستخدم
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            // ربط اختياري مع مرجع AI
            $table->foreignId('plant_reference_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // اسم النبتة الذي يدخله المستخدم (اختياري)
            $table->string('custom_name')->nullable();

            // صورة النبتة من جهاز المستخدم
            $table->string('image')->nullable();

            // توصيات السقاية الناتجة عن AI (Days)
            $table->integer('watering_frequency_days')->nullable();

            // أحدث موعد يجب فيه سقايتها
            $table->date('next_watering_date')->nullable();

            // ملاحظات إضافية من المستخدم
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plants');
    }
};
