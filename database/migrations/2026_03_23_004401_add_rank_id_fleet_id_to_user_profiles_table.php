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
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->foreignId('rank_id')->nullable()->after('blood_type')->constrained('ranks')->onDelete('set null');
            $table->foreignId('fleet_id')->nullable()->after('rank_id')->constrained('fleets')->onDelete('set null');
            $table->index('rank_id');
            $table->index('fleet_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropForeign(['rank_id']);
            $table->dropForeign(['fleet_id']);
            $table->dropIndex(['rank_id']);
            $table->dropIndex(['fleet_id']);
            $table->dropColumn(['rank_id', 'fleet_id']);
        });
    }
};
