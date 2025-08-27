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
        Schema::table('universities', function (Blueprint $table) {
            // Check if columns exist before dropping them
            if (Schema::hasColumn('universities', 'city_id')) {
                // Try to drop foreign key constraint if it exists
                try {
                    $table->dropForeign(['city_id']);
                } catch (\Exception $e) {
                    // Foreign key constraint might not exist
                }
            }

            // Remove the specified columns if they exist
            $columnsToRemove = [];
            if (Schema::hasColumn('universities', 'type')) {
                $columnsToRemove[] = 'type';
            }
            if (Schema::hasColumn('universities', 'address')) {
                $columnsToRemove[] = 'address';
            }
            if (Schema::hasColumn('universities', 'city_id')) {
                $columnsToRemove[] = 'city_id';
            }

            if (!empty($columnsToRemove)) {
                $table->dropColumn($columnsToRemove);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            // Re-add the columns
            $table->string('type')->nullable();
            $table->string('address')->nullable();
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null');
        });
    }
};
