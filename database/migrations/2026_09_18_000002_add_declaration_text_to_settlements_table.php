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
                if (!Schema::hasColumn('full_and_final_settlements', 'declaration_text')) {
                    $table->longText('declaration_text')->nullable()->after('custom_fields_data');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('full_and_final_settlements')) {
            Schema::table('full_and_final_settlements', function (Blueprint $table) {
                if (Schema::hasColumn('full_and_final_settlements', 'declaration_text')) {
                    $table->dropColumn('declaration_text');
                }
            });
        }
    }
};
