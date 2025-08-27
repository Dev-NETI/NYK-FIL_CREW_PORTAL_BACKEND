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
            // First drop the foreign key constraint
            $table->dropForeign(['fleet_id']);
            // Drop the fleet_id column
            $table->dropColumn('fleet_id');
            // Re-add fleet_id after crew_id
            $table->foreignId('fleet_id')->nullable()->after('crew_id')->constrained('fleets')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['fleet_id']);
            // Drop the fleet_id column
            $table->dropColumn('fleet_id');
            // Re-add fleet_id at the end (original position)
            $table->foreignId('fleet_id')->nullable()->constrained('fleets')->onDelete('set null');
        });
    }
};
