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
        Schema::table('users', function (Blueprint $table) {
            // Personal Information
            $table->string('crew_id', 20)->unique()->nullable()->after('id');
            $table->string('first_name')->nullable()->after('name');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');
            $table->string('suffix')->nullable()->after('last_name'); // Jr., Sr., III, etc.
            $table->date('date_of_birth')->nullable()->after('email');
            $table->integer('age')->nullable()->after('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('age');
            $table->string('mobile_number', 20)->nullable()->after('gender');
            $table->string('alternative_mobile', 20)->nullable()->after('mobile_number');

            // Address relationship
            $table->foreignId('permanent_address_id')->nullable()->constrained('addresses')->onDelete('set null')->after('alternative_mobile');

            // Education
            $table->foreignId('graduated_school_id')->nullable()->constrained('schools')->onDelete('set null')->after('permanent_address_id');
            $table->date('date_graduated')->nullable()->after('graduated_school_id');
            $table->string('course_degree')->nullable()->after('date_graduated');

            // Employment Status
            $table->enum('crew_status', ['active', 'on_leave', 'resigned', 'terminated', 'retired'])->default('active')->after('course_degree');
            $table->enum('hire_status', ['hired', 'candidate', 'interview', 'rejected', 'on_hold'])->default('candidate')->after('crew_status');
            $table->date('hire_date')->nullable()->after('hire_status');

            // Documents and IDs
            $table->string('passport_number')->nullable()->after('hire_date');
            $table->date('passport_expiry')->nullable()->after('passport_number');
            $table->string('seaman_book_number')->nullable()->after('passport_expiry');
            $table->date('seaman_book_expiry')->nullable()->after('seaman_book_number');

            // Emergency contact - primary allotee
            $table->foreignId('primary_allotee_id')->nullable()->constrained('allotees')->onDelete('set null')->after('seaman_book_expiry');

            // System fields
            $table->boolean('is_active')->default(true)->after('primary_allotee_id');

            // Indexes for better performance
            $table->index(['crew_status', 'hire_status']);
            $table->index(['date_of_birth']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['crew_status', 'hire_status']);
            $table->dropIndex(['date_of_birth']);

            // Drop foreign keys
            $table->dropForeign(['permanent_address_id']);
            $table->dropForeign(['graduated_school_id']);
            $table->dropForeign(['primary_allotee_id']);

            // Drop columns
            $table->dropColumn([
                'crew_id',
                'first_name',
                'middle_name',
                'last_name',
                'suffix',
                'date_of_birth',
                'age',
                'gender',
                'mobile_number',
                'alternative_mobile',
                'permanent_address_id',
                'graduated_school_id',
                'date_graduated',
                'course_degree',
                'crew_status',
                'hire_status',
                'hire_date',
                'passport_number',
                'passport_expiry',
                'seaman_book_number',
                'seaman_book_expiry',
                'primary_allotee_id',
                'is_active'
            ]);
        });
    }
};
