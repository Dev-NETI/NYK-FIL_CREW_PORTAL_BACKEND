<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clean up any remaining foreign key constraints on modified_by columns
        $this->cleanUpForeignKeys();
    }

    private function cleanUpForeignKeys()
    {
        $tables = ['users', 'addresses', 'contracts', 'vessels', 'allotees', 'crew_allotees'];

        foreach ($tables as $tableName) {
            // Check if table exists
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            // Check if modified_by column exists
            if (! Schema::hasColumn($tableName, 'modified_by')) {
                continue;
            }

            // Use raw SQL to check and drop foreign keys more safely
            $this->dropForeignKeysForTable($tableName);
        }
    }

    private function dropForeignKeysForTable($tableName)
    {
        try {
            // Get all foreign keys for this table that reference modified_by
            $sql = "
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = ? 
                AND COLUMN_NAME = 'modified_by'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ";

            $foreignKeys = DB::select($sql, [$tableName]);

            foreach ($foreignKeys as $fk) {
                try {
                    DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                    echo "Dropped foreign key {$fk->CONSTRAINT_NAME} from {$tableName}\n";
                } catch (\Exception $e) {
                    // Continue if constraint doesn't exist
                }
            }
        } catch (\Exception $e) {
            // Continue to next table if there's an error
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration only removes foreign keys
        // No rollback needed as we've moved to text-based system
    }
};
