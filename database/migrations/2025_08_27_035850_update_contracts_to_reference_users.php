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
            // Drop the existing crew_id foreign key and column
            $table->dropForeign(['crew_id']);
            $table->dropColumn('crew_id');

            // Add user_id foreign key instead
            $table->foreignId('user_id')->after('contract_number')->constrained('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Drop user_id foreign key and column
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');

            // Add back crew_id foreign key
            $table->foreignId('crew_id')->after('contract_number')->constrained('crew')->onDelete('cascade');
        });
    }
};
