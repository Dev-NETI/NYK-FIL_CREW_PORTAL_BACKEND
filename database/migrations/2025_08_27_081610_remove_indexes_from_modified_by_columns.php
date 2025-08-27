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
        $tables = ['users', 'addresses', 'contracts', 'vessels', 'allotees', 'crew_allotees'];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'modified_by')) {
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
                    // Drop the index
                    DB::statement("DROP INDEX `{$index->Key_name}` ON `{$tableName}`");
                    echo "Dropped index {$index->Key_name} from {$tableName}\n";
                } catch (\Exception $e) {
                    // Index might not exist or might be a primary key
                    echo "Could not drop index {$index->Key_name} from {$tableName}: " . $e->getMessage() . "\n";
                }
            }

            // Alternative approach using Laravel's Schema builder
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                try {
                    $table->dropIndex(['modified_by']);
                    echo "Dropped modified_by index from {$tableName} using Schema builder\n";
                } catch (\Exception $e) {
                    // Index doesn't exist
                }

                // Try to drop other possible index names
                $possibleIndexNames = [
                    $tableName . '_modified_by_index',
                    $tableName . '_modified_by_foreign'
                ];

                foreach ($possibleIndexNames as $indexName) {
                    try {
                        $table->dropIndex($indexName);
                        echo "Dropped index {$indexName} from {$tableName}\n";
                    } catch (\Exception $e) {
                        // Index doesn't exist
                    }
                }
            });
        } catch (\Exception $e) {
            echo "Error processing table {$tableName}: " . $e->getMessage() . "\n";
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
