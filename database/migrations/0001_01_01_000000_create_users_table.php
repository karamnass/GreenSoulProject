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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            // اسم المستخدم (اختياري في البداية)
            $table->string('name')->nullable();
            // رقم الموبايل هو الأساس في النظام
            $table->string('phone', 20)->unique();
            // حالة الحساب فعال / غير فعال
            $table->boolean('is_active')->default(true);
            $table->timestamp('phone_verified_at')->nullable();
            $table->foreignId('role_id')
                ->nullable()
                ->index();
            //  ->constrained('roles')
            //->nullOnDelete();
            $table->rememberToken();
            $table->timestamps();
        });
    }
};
