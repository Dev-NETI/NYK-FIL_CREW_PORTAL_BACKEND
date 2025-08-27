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
        Schema::table('contracts', function (Blueprint $table) {
            // Drop foreign key constraint first if exists
            try {
                $table->dropForeign(['previous_contract_id']);
            } catch (\Exception $e) {
                // Foreign key constraint might not exist
            }

            // Remove the specified columns
            $table->dropColumn([
                'status',
                'contract_type',
                'basic_salary',
                'overtime_rate',
                'currency',
                'previous_contract_id',
                'remarks',
                'termination_reason'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Re-add the columns
            $table->enum('status', ['active', 'completed', 'terminated', 'expired'])->default('active');
            $table->enum('contract_type', ['new', 'extension', 'renewal'])->default('new');
            $table->decimal('basic_salary', 10, 2)->nullable();
            $table->decimal('overtime_rate', 8, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->foreignId('previous_contract_id')->nullable()->constrained('contracts')->onDelete('set null');
            $table->text('remarks')->nullable();
            $table->string('termination_reason')->nullable();
        });
    }
};
