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
                if (!Schema::hasColumn('full_and_final_settlements', 'policy_rules_text')) {
                    $table->text('policy_rules_text')->nullable()->after('declaration_text');
                }
                if (!Schema::hasColumn('full_and_final_settlements', 'policy_rules_link')) {
                    $table->text('policy_rules_link')->nullable()->after('policy_rules_text');
                }
                if (!Schema::hasColumn('full_and_final_settlements', 'policy_rules_title')) {
                    $table->string('policy_rules_title')->nullable()->after('policy_rules_link');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('full_and_final_settlements')) {
            Schema::table('full_and_final_settlements', function (Blueprint $table) {
                $columns = ['policy_rules_text', 'policy_rules_link', 'policy_rules_title'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('full_and_final_settlements', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
