<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the specified columns
            $table->dropColumn(['name', 'alternative_mobile', 'course_degree']);
        });

        // Update enum values for crew_status
        DB::statement("ALTER TABLE users MODIFY COLUMN crew_status ENUM('on_board', 'on_vacation', 'finished_contract_with_further_medical_attention') DEFAULT 'on_board'");

        // Update enum values for hire_status
        DB::statement("ALTER TABLE users MODIFY COLUMN hire_status ENUM('new_hire', 're_hire') DEFAULT 'new_hire'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add back the removed columns
            $table->string('name')->nullable()->after('crew_id');
            $table->string('alternative_mobile', 20)->nullable()->after('mobile_number');
            $table->string('course_degree')->nullable()->after('date_graduated');
        });

        // Restore original enum values for crew_status
        DB::statement("ALTER TABLE users MODIFY COLUMN crew_status ENUM('active', 'on_leave', 'resigned', 'terminated', 'retired') DEFAULT 'active'");

        // Restore original enum values for hire_status
        DB::statement("ALTER TABLE users MODIFY COLUMN hire_status ENUM('hired', 'candidate', 'interview', 'rejected', 'on_hold') DEFAULT 'candidate'");
    }
};
