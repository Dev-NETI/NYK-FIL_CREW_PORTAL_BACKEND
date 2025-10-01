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
        Schema::create('job_description_requests', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->unsignedBigInteger('crew_id');
            $table->enum('purpose', ['SSS', 'PAG_IBIG', 'PHILHEALTH', 'VISA']);
            $table->enum('visa_type', ['TOURIST', 'BUSINESS', 'WORK', 'TRANSIT', 'STUDENT', 'FAMILY', 'SEAMAN'])->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'ready_for_approval', 'approved', 'disapproved'])->default('pending');
            
            // Document information (filled by EA)
            $table->string('memo_no', 100)->nullable();
            
            // Processing information
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('processed_date')->nullable();
            
            // VP approval information
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_date')->nullable();
            $table->text('disapproval_reason')->nullable();
            $table->text('vp_comments')->nullable();
            $table->boolean('signature_added')->default(false);
            
            // Audit fields
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraints
            $table->foreign('crew_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes for performance
            $table->index('crew_id');
            $table->index('status');
            $table->index('created_at');
            $table->index('processed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_description_requests');
    }
};
