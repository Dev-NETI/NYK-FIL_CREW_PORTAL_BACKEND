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
        $tables = ['users', 'addresses', 'contracts', 'vessels', 'allotees', 'crew_allotees'];

        foreach ($tables as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'modified_by')) {
                continue;
            }

            $this->removeIndexesForTable($tableName);
        }
    }

    private function removeIndexesForTable($tableName)
    {
        try {
            // Get all indexes on modified_by column for this table
            $indexes = DB::select("
                SHOW INDEX FROM `{$tableName}` 
                WHERE Column_name = 'modified_by'
            ");

            foreach ($indexes as $index) {
                try {
                    // Use raw SQL to drop the index safely
                    DB::statement("ALTER TABLE `{$tableName}` DROP INDEX `{$index->Key_name}`");
                    echo "Dropped index {$index->Key_name} from {$tableName}\n";
                } catch (\Exception $e) {
                    // Index might not exist or might be a primary key
                    echo "Could not drop index {$index->Key_name} from {$tableName}: skipping\n";
                }
            }

            // Check if there are any remaining indexes we might have missed
            $remainingIndexes = DB::select("
                SHOW INDEX FROM `{$tableName}` 
                WHERE Column_name = 'modified_by'
            ");
            
            if (empty($remainingIndexes)) {
                echo "All modified_by indexes removed from {$tableName}\n";
            } else {
                echo "Some modified_by indexes still remain on {$tableName} (might be system-managed)\n";
            }
            
        } catch (\Exception $e) {
            echo "Error processing table {$tableName}: ".$e->getMessage()."\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't want to recreate indexes on modified_by since it's now a text field
        // for storing user names, not for referencing users
    }
};
