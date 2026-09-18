<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('full_and_final_settlements')) {
            Schema::create('full_and_final_settlements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('created_by')->default(0);
                $table->unsignedBigInteger('employee_id');
                $table->string('settlement_number', 50)->unique();
                $table->string('industry_type', 50)->default('software');
                $table->string('status', 30)->default('draft');
                
                $table->string('employee_name')->nullable();
                $table->string('employee_code')->nullable();
                $table->string('designation')->nullable();
                $table->string('department')->nullable();
                $table->date('date_of_joining')->nullable();
                $table->date('last_working_day')->nullable();
                $table->string('reason_for_separation')->nullable();

                $table->json('earnings_data')->nullable();
                $table->json('deductions_data')->nullable();
                $table->decimal('gross_payable', 15, 2)->default(0);
                $table->decimal('total_deductions', 15, 2)->default(0);
                $table->decimal('net_amount', 15, 2)->default(0);

                $table->json('clearance_data')->nullable();

                $table->boolean('employee_declaration_accepted')->default(false);
                $table->longText('employee_signature')->nullable();
                $table->timestamp('employee_signed_at')->nullable();
                $table->string('employee_signed_ip', 60)->nullable();
                $table->text('employee_remarks')->nullable();

                $table->string('hr_representative_name')->nullable();
                $table->longText('hr_signature')->nullable();
                $table->timestamp('hr_cleared_at')->nullable();
                $table->string('final_settlement_status', 30)->default('Pending');
                $table->date('payment_date')->nullable();
                $table->string('payment_mode', 50)->nullable();
                $table->string('payment_reference_no')->nullable();

                $table->string('authorized_signatory_name')->nullable();
                $table->longText('authorized_signature')->nullable();
                $table->date('authorized_date')->nullable();

                $table->string('sharing_token', 80)->nullable()->unique();
                $table->timestamp('token_expires_at')->nullable();
                $table->timestamp('link_shared_at')->nullable();

                $table->timestamps();

                $table->index(['created_by', 'employee_id']);
                $table->index('status');
                $table->index('sharing_token');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('full_and_final_settlements');
    }
};
