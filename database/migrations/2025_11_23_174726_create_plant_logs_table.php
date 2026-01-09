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
        Schema::create('plant_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plant_id')
                ->constrained()
                ->onDelete('cascade');

            // نوع النشاط: watering / fertilizing / rename / photo_update / ai_analysis ...
            $table->string('action_type');

            // قيمة إضافية (اختياري)
            $table->text('details')->nullable();

            // تاريخ الحدث
            $table->timestamp('logged_at')->default(now());

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plant_logs');
    }
};
