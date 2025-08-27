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
        Schema::create('user_allotees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('allotee_id')->constrained('allotees')->onDelete('cascade');
            $table->decimal('allotment_percentage', 5, 2)->nullable(); // Percentage of salary to allot
            $table->decimal('fixed_amount', 10, 2)->nullable(); // Fixed amount to allot
            $table->string('allotment_type')->default('percentage'); // 'percentage' or 'fixed'
            $table->boolean('is_primary')->default(false); // Primary allotee
            $table->boolean('is_emergency_contact')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Ensure unique user-allotee combination
            $table->unique(['user_id', 'allotee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_allotees');
    }
};
