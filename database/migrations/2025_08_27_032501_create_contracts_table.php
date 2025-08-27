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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number', 50)->unique();

            // Crew and Vessel relationships
            $table->foreignId('crew_id')->constrained('crew')->onDelete('cascade');
            $table->foreignId('vessel_id')->constrained('vessels')->onDelete('restrict');
            $table->foreignId('rank_id')->constrained('ranks')->onDelete('restrict');

            // Contract dates
            $table->date('departure_date')->nullable();
            $table->date('arrival_date')->nullable();
            $table->integer('duration_months'); // Contract duration in months
            $table->date('contract_start_date');
            $table->date('contract_end_date');

            // Status and type
            $table->enum('status', ['pending', 'active', 'completed', 'terminated', 'extended'])->default('pending');
            $table->enum('contract_type', ['new', 'extension', 'promotion', 'transfer'])->default('new');

            // Financial
            $table->decimal('basic_salary', 10, 2)->nullable();
            $table->decimal('overtime_rate', 8, 2)->nullable();
            $table->string('currency', 3)->default('USD');

            // Previous contract reference (for extensions/renewals)
            $table->foreignId('previous_contract_id')->nullable()->constrained('contracts')->onDelete('set null');

            // Notes and remarks
            $table->text('remarks')->nullable();
            $table->text('termination_reason')->nullable();

            // System fields
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['crew_id', 'status']);
            $table->index(['vessel_id', 'status']);
            $table->index(['contract_start_date', 'contract_end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
