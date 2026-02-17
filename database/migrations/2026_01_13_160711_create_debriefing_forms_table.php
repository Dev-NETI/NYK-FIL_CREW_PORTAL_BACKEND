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
        Schema::create('debriefing_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crew_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->enum('status', ['draft', 'submitted', 'confirmed'])->default('draft')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('rank')->nullable();
            $table->string('crew_name')->nullable();
            $table->string('vessel_type')->nullable();
            $table->string('principal_name')->nullable();
            $table->string('embarkation_vessel_name')->nullable();
            $table->string('embarkation_place')->nullable();
            $table->date('embarkation_date')->nullable();
            $table->date('disembarkation_date')->nullable();
            $table->string('disembarkation_place')->nullable();
            $table->date('manila_arrival_date')->nullable();
            $table->text('present_address')->nullable();
            $table->text('provincial_address')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->date('date_of_availability')->nullable();
            $table->string('availability_status')->nullable();
            $table->date('next_vessel_assignment_date')->nullable();
            $table->text('long_vacation_reason')->nullable();
            $table->boolean('has_illness_or_injury')->default(false);
            $table->json('illness_injury_types')->nullable();
            $table->unsignedInteger('lost_work_days')->nullable();
            $table->text('medical_incident_details')->nullable();
            $table->text('comment_q1_technical')->nullable();
            $table->text('comment_q2_crewing')->nullable();
            $table->text('comment_q3_complaint')->nullable();
            $table->text('comment_q4_immigrant_visa')->nullable();
            $table->text('comment_q5_commitments')->nullable();
            $table->text('comment_q6_additional')->nullable();

            // Optional: store signature file path (image) or base64 later if needed
            $table->string('signature_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamp('pdf_generated_at')->nullable();
            $table->enum('pdf_status', ['pending', 'generating', 'ready', 'failed'])->nullable();
            $table->text('pdf_error')->nullable();
            $table->timestamp('pdf_emailed_at')->nullable();
            $table->timestamps();
            $table->index(['crew_id', 'status']);
            $table->index('department_id');
            $table->index(['status', 'submitted_at']);
            $table->index(['confirmed_by', 'confirmed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debriefing_forms');
    }
};
