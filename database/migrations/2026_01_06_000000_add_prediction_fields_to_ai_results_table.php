<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_results', function (Blueprint $table) {
            // Add fields safely without breaking existing deployments.
            if (! Schema::hasColumn('ai_results', 'plant_name')) {
                $table->string('plant_name')->nullable()->after('extra_data');
            }

            if (! Schema::hasColumn('ai_results', 'confidence')) {
                $table->decimal('confidence', 6, 4)->nullable()->after('plant_name');
            }

            if (! Schema::hasColumn('ai_results', 'recommendation')) {
                $table->text('recommendation')->nullable()->after('confidence');
            }

            if (! Schema::hasColumn('ai_results', 'raw_response')) {
                $table->json('raw_response')->nullable()->after('recommendation');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ai_results', function (Blueprint $table) {
            if (Schema::hasColumn('ai_results', 'raw_response')) {
                $table->dropColumn('raw_response');
            }

            if (Schema::hasColumn('ai_results', 'recommendation')) {
                $table->dropColumn('recommendation');
            }

            if (Schema::hasColumn('ai_results', 'confidence')) {
                $table->dropColumn('confidence');
            }

            if (Schema::hasColumn('ai_results', 'plant_name')) {
                $table->dropColumn('plant_name');
            }
        });
    }
};
