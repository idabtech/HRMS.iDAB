<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('full_and_final_settlements')) {
            Schema::table('full_and_final_settlements', function (Blueprint $table) {
                if (!Schema::hasColumn('full_and_final_settlements', 'custom_fields_schema')) {
                    $table->json('custom_fields_schema')->nullable()->after('clearance_data');
                }
                if (!Schema::hasColumn('full_and_final_settlements', 'custom_fields_data')) {
                    $table->json('custom_fields_data')->nullable()->after('custom_fields_schema');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('full_and_final_settlements')) {
            Schema::table('full_and_final_settlements', function (Blueprint $table) {
                if (Schema::hasColumn('full_and_final_settlements', 'custom_fields_schema')) {
                    $table->dropColumn('custom_fields_schema');
                }
                if (Schema::hasColumn('full_and_final_settlements', 'custom_fields_data')) {
                    $table->dropColumn('custom_fields_data');
                }
            });
        }
    }
};