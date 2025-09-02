<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // List of all tables with modified_by column
        $tables = ['users', 'addresses', 'contracts', 'vessels', 'allotees', 'crew_allotees'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // Drop foreign key constraint if it exists
                try {
                    $table->dropForeign(['modified_by']);
                } catch (\Exception $e) {
                    // Foreign key constraint might not exist
                }

                // Change modified_by to text
                $table->string('modified_by')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // List of all tables with modified_by column
        $tables = ['users', 'addresses', 'contracts', 'vessels', 'allotees', 'crew_allotees'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // Change modified_by back to foreign key
                $table->bigInteger('modified_by')->nullable()->change();
                $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }
};
