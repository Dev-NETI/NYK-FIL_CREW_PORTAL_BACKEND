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
        Schema::table('travel_documents', function (Blueprint $table) {
            $table->boolean('is_US_VISA')->default(false)->after('remaining_pages');
            $table->enum('visa_type', ['C1/D'])->nullable()->after('is_US_VISA');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_documents', function (Blueprint $table) {
            $table->dropColumn(['is_US_VISA', 'visa_sub_type']);
        });
    }
};
