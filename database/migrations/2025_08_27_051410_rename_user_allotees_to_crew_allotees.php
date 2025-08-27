<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename the table from user_allotees to crew_allotees
        Schema::rename('user_allotees', 'crew_allotees');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename back from crew_allotees to user_allotees
        Schema::rename('crew_allotees', 'user_allotees');
    }
};
