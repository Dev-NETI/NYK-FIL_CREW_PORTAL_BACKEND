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
        Schema::table('crew_allotees', function (Blueprint $table) {
            // Add modified_by column
            $table->foreignId('modified_by')->nullable()->constrained('users')->onDelete('set null')->after('is_emergency_contact');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crew_allotees', function (Blueprint $table) {
            // Remove modified_by foreign key and column
            $table->dropForeign(['modified_by']);
            $table->dropColumn('modified_by');
        });
    }
};
