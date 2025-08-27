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
        Schema::create('allotees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('relationship'); // spouse, parent, child, sibling, etc.
            $table->string('mobile_number', 20)->nullable();
            $table->string('email')->nullable();
            $table->foreignId('address_id')->nullable()->constrained('addresses')->onDelete('set null');
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('id_type')->nullable(); // valid_id, passport, driver_license, etc.
            $table->string('id_number')->nullable();
            $table->boolean('is_emergency_contact')->default(false);
            $table->boolean('is_beneficiary')->default(false);
            $table->decimal('beneficiary_percentage', 5, 2)->nullable(); // For allotment percentage
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allotees');
    }
};
