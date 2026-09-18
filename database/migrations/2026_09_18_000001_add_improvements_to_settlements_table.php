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
                if (!Schema::hasColumn('full_and_final_settlements', 'manager_name')) {
                    $table->string('manager_name')->nullable()->after('employee_remarks');
                }
                if (!Schema::hasColumn('full_and_final_settlements', 'manager_signature')) {
                    $table->longText('manager_signature')->nullable()->after('manager_name');
                }
                if (!Schema::hasColumn('full_and_final_settlements', 'manager_signed_at')) {
                    $table->timestamp('manager_signed_at')->nullable()->after('manager_signature');
                }
                if (!Schema::hasColumn('full_and_final_settlements', 'manager_remarks')) {
                    $table->text('manager_remarks')->nullable()->after('manager_signed_at');
                }
                if (!Schema::hasColumn('full_and_final_settlements', 'activity_logs')) {
                    $table->json('activity_logs')->nullable()->after('link_shared_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('full_and_final_settlements')) {
            Schema::table('full_and_final_settlements', function (Blueprint $table) {
                $columns = ['manager_name', 'manager_signature', 'manager_signed_at', 'manager_remarks', 'activity_logs'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('full_and_final_settlements', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
