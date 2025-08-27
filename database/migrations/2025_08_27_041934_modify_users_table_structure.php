<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, drop the is_active column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        // Add the new columns and modify structure
        Schema::table('users', function (Blueprint $table) {
            // Add modified_by column
            $table->foreignId('modified_by')->nullable()->constrained('users')->onDelete('set null');

            // Add soft deletes
            $table->softDeletes();
        });

        // Now we need to reorder the columns to move timestamps to the end
        // Since MySQL doesn't have a direct way to reorder all columns easily,
        // we'll use raw SQL to recreate the table structure
        DB::statement("
            ALTER TABLE users 
            MODIFY COLUMN created_at timestamp NULL AFTER deleted_at,
            MODIFY COLUMN updated_at timestamp NULL AFTER created_at
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove soft deletes
            $table->dropSoftDeletes();

            // Remove modified_by foreign key and column
            $table->dropForeign(['modified_by']);
            $table->dropColumn('modified_by');

            // Add back is_active column
            $table->boolean('is_active')->default(true);
        });

        // Move timestamps back to their original positions
        DB::statement("
            ALTER TABLE users 
            MODIFY COLUMN created_at timestamp NULL AFTER remember_token,
            MODIFY COLUMN updated_at timestamp NULL AFTER created_at
        ");
    }
};
