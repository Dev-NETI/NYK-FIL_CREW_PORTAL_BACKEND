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
        Schema::table('contracts', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['rank_id']);
            // Remove rank_id column
            $table->dropColumn('rank_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Re-add rank_id column
            $table->foreignId('rank_id')->nullable()->constrained('ranks')->onDelete('set null');
        });
    }
};
