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
        Schema::table('crew_allotees', function (Blueprint $table) {
            // Remove the allotment fields
            $table->dropColumn([
                'allotment_percentage',
                'fixed_amount',
                'allotment_type',
                'is_active'
            ]);

            // Add soft deletes
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crew_allotees', function (Blueprint $table) {
            // Remove soft deletes
            $table->dropSoftDeletes();

            // Add back the removed columns
            $table->decimal('allotment_percentage', 5, 2)->nullable();
            $table->decimal('fixed_amount', 10, 2)->nullable();
            $table->string('allotment_type')->default('percentage');
            $table->boolean('is_active')->default(true);
        });
    }
};
