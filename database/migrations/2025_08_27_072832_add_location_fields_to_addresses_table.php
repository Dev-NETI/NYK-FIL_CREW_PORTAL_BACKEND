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
        Schema::table('addresses', function (Blueprint $table) {
            // Add location fields before city_id
            $table->foreignId('island_id')->nullable()->after('street_address')->constrained('islands')->onDelete('set null');
            $table->foreignId('region_id')->nullable()->after('island_id')->constrained('regions')->onDelete('set null');
            $table->foreignId('province_id')->nullable()->after('region_id')->constrained('provinces')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['island_id']);
            $table->dropForeign(['region_id']);
            $table->dropForeign(['province_id']);
            // Remove the columns
            $table->dropColumn(['island_id', 'region_id', 'province_id']);
        });
    }
};
