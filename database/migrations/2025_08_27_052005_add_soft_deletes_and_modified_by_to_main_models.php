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
        // List of tables that need both soft deletes and modified_by
        $tables = [
            'addresses',
            'allotees',
            'cities',
            'contracts',
            'fleets',
            'islands',
            'provinces',
            'ranks',
            'rank_categories',
            'rank_groups',
            'regions',
            'schools',
            'vessels',
            'vessel_types'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // Drop is_active column if it exists
                if (Schema::hasColumn($table->getTable(), 'is_active')) {
                    $table->dropColumn('is_active');
                }

                // Add modified_by column
                $table->foreignId('modified_by')->nullable()->constrained('users')->onDelete('set null');

                // Add soft deletes
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // List of tables to revert
        $tables = [
            'addresses',
            'allotees',
            'cities',
            'contracts',
            'fleets',
            'islands',
            'provinces',
            'ranks',
            'rank_categories',
            'rank_groups',
            'regions',
            'schools',
            'vessels',
            'vessel_types'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // Remove soft deletes
                $table->dropSoftDeletes();

                // Remove modified_by foreign key and column
                $table->dropForeign(['modified_by']);
                $table->dropColumn('modified_by');

                // Add back is_active column
                $table->boolean('is_active')->default(true);
            });
        }
    }
};
