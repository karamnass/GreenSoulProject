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
        Schema::create('ai_results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plant_id')
                ->constrained()
                ->onDelete('cascade');

            // وصف AI
            $table->text('description')->nullable();

            // صورة من الـ AI
            $table->string('image')->nullable();

            // دقة AI (0-100)
            $table->decimal('accuracy', 5, 2)->nullable();

            // JSON إضافي مثل: {lighting:"medium", toxicity:"safe", ...}
            $table->json('extra_data')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_results');
    }
};
