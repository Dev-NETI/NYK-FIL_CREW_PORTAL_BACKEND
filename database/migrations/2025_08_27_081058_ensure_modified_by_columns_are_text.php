<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
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

            // Check current column type
            $columns = DB::select("SHOW COLUMNS FROM `{$tableName}` LIKE 'modified_by'");

            if (! empty($columns) && $columns[0]->Type !== 'text') {
                // Get all indexes on the modified_by column
                $indexes = DB::select("SHOW INDEX FROM `{$tableName}` WHERE Column_name = 'modified_by'");

                Schema::table($tableName, function (Blueprint $table) use ($indexes, $tableName) {
                    // Drop any existing indexes on the modified_by column
                    foreach ($indexes as $index) {
                        try {
                            // Use the actual index name from the database
                            DB::statement("ALTER TABLE `{$tableName}` DROP INDEX `{$index->Key_name}`");
                        } catch (\Exception $e) {
                            // Ignore if index doesn't exist or can't be dropped
                        }
                    }

                    $table->text('modified_by')->nullable()->change();
                });
                echo "Changed modified_by to text in {$tableName}\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration ensures text type - no rollback needed
    }
};
