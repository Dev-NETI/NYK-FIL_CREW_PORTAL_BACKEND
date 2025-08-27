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
        // Drop the old crew_allotees pivot table first (because it has foreign keys)
        Schema::dropIfExists('crew_allotees');

        // Drop the crew table
        Schema::dropIfExists('crew');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This would be complex to reverse, so we'll just note it
        // If needed, the crew table structure can be recreated from the previous migration
        throw new Exception('Cannot reverse dropping crew tables. Please restore from backup or recreate manually.');
    }
};
