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
        Schema::table('vessels', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['fleet_id']);
            // Remove the specified fields
            $table->dropColumn([
                'fleet_id',
                'flag_state',
                'gross_tonnage',
                'deadweight_tonnage',
                'built_year',
                'shipyard',
                'status',
                'specifications'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vessels', function (Blueprint $table) {
            // Re-add the fields in reverse order
            $table->foreignId('fleet_id')->constrained('fleets')->onDelete('restrict');
            $table->string('flag_state')->nullable();
            $table->integer('gross_tonnage')->nullable();
            $table->integer('deadweight_tonnage')->nullable();
            $table->year('built_year')->nullable();
            $table->string('shipyard')->nullable();
            $table->enum('status', ['active', 'maintenance', 'drydock', 'decommissioned'])->default('active');
            $table->text('specifications')->nullable();
        });
    }
};
