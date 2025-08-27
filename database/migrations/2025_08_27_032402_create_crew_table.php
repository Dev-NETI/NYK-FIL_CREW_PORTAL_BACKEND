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
        Schema::create('crew', function (Blueprint $table) {
            $table->id();
            $table->string('crew_id', 20)->unique(); // Unique crew identifier
            $table->string('name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable(); // Jr., Sr., III, etc.
            $table->date('date_of_birth');
            $table->integer('age')->nullable(); // Can be calculated from DOB
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('email')->unique();
            $table->string('mobile_number', 20);
            $table->string('alternative_mobile', 20)->nullable();

            // Address relationship
            $table->foreignId('permanent_address_id')->nullable()->constrained('addresses')->onDelete('set null');

            // Education
            $table->foreignId('graduated_school_id')->nullable()->constrained('schools')->onDelete('set null');
            $table->date('date_graduated')->nullable();
            $table->string('course_degree')->nullable();

            // Employment Status
            $table->enum('crew_status', ['active', 'on_leave', 'resigned', 'terminated', 'retired'])->default('active');
            $table->enum('hire_status', ['hired', 'candidate', 'interview', 'rejected', 'on_hold'])->default('candidate');
            $table->date('hire_date')->nullable();

            // Documents and IDs
            $table->string('passport_number')->nullable();
            $table->date('passport_expiry')->nullable();
            $table->string('seaman_book_number')->nullable();
            $table->date('seaman_book_expiry')->nullable();

            // Emergency contact - can have multiple allotees
            $table->foreignId('primary_allotee_id')->nullable()->constrained('allotees')->onDelete('set null');

            // System fields
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Link to auth user
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes for better performance
            $table->index(['crew_status', 'hire_status']);
            $table->index(['date_of_birth']);
            $table->index(['email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crew');
    }
};
