<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('crew', function (Blueprint $table) {
            // Add missing foreign key constraints that failed during initial migration

            // Add foreign key for primary_allotee_id
            if (! $this->foreignKeyExists('crew', 'crew_primary_allotee_id_foreign')) {
                $table->foreign('primary_allotee_id')->references('id')->on('allotees')->onDelete('set null');
            }

            // Add foreign key for user_id
            if (! $this->foreignKeyExists('crew', 'crew_user_id_foreign')) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Check if a foreign key exists
     */
    private function foreignKeyExists($table, $foreign_key): bool
    {
        $result = DB::select(
            'SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?',
            [config('database.connections.mysql.database'), $table, $foreign_key]
        );

        return count($result) > 0;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crew', function (Blueprint $table) {
            // Drop the foreign keys
            $table->dropForeign(['primary_allotee_id']);
            $table->dropForeign(['user_id']);
        });
    }
};
