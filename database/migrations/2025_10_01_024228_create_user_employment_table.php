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
        Schema::create('user_employment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('fleet_id')->nullable()->constrained('fleets')->onDelete('set null');
            $table->foreignId('rank_id')->nullable()->constrained('ranks')->onDelete('set null');
            $table->enum('crew_status', ['on_board', 'on_vacation', 'standby', 'resigned', 'terminated'])->nullable();
            $table->enum('hire_status', ['new_hire', 're_hire', 'promoted', 'transferred'])->nullable();
            $table->date('hire_date')->nullable();
            $table->string('passport_number')->nullable();
            $table->date('passport_expiry')->nullable();
            $table->string('seaman_book_number')->nullable();
            $table->date('seaman_book_expiry')->nullable();
            $table->foreignId('primary_allotee_id')->nullable()->constrained('allotees')->onDelete('set null');
            $table->decimal('basic_salary', 10, 2)->nullable();
            $table->text('employment_notes')->nullable();
            
            // Audit fields
            $table->foreignId('modified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('user_id');
            $table->index('fleet_id');
            $table->index('rank_id');
            $table->index('crew_status');
            $table->index('hire_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_employment');
    }
};
