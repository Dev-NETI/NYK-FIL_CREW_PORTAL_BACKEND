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
        Schema::create('vessels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('vessel_id', 20)->unique(); // IMO number or vessel identifier
            $table->foreignId('vessel_type_id')->constrained('vessel_types')->onDelete('restrict');
            $table->foreignId('fleet_id')->constrained('fleets')->onDelete('restrict');
            $table->string('flag_state')->nullable();
            $table->integer('gross_tonnage')->nullable();
            $table->integer('deadweight_tonnage')->nullable();
            $table->year('built_year')->nullable();
            $table->string('shipyard')->nullable();
            $table->enum('status', ['active', 'maintenance', 'drydock', 'decommissioned'])->default('active');
            $table->text('specifications')->nullable(); // JSON field for additional specs
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vessels');
    }
};
