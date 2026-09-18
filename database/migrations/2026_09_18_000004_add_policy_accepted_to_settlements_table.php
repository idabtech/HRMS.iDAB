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
                if (!Schema::hasColumn('full_and_final_settlements', 'policy_rules_accepted')) {
                    $table->boolean('policy_rules_accepted')->default(false)->after('policy_rules_title');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('full_and_final_settlements')) {
            Schema::table('full_and_final_settlements', function (Blueprint $table) {
                if (Schema::hasColumn('full_and_final_settlements', 'policy_rules_accepted')) {
                    $table->dropColumn('policy_rules_accepted');
                }
            });
        }
    }
};
