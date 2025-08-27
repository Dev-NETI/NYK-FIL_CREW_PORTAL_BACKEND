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
        Schema::create('fleets', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Fleet A, Fleet B, Main Fleet, etc.
            $table->string('code', 10)->unique(); // FA, FB, MF, etc.
            $table->string('description')->nullable();
            $table->string('manager_name')->nullable();
            $table->string('manager_contact')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fleets');
    }
};
