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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('type');
            $table->string('full_address')->nullable();
            $table->string('street_address')->nullable();
            $table->string('barangay')->nullable();
            $table->string('zip_code', 10)->nullable();
            $table->string('landmark')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('brgy_id')->nullable();
            $table->string('city_id')->nullable();
            $table->string('province_id')->nullable();
            $table->string('region_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
