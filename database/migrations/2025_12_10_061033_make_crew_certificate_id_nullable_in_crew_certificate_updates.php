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
        Schema::table('crew_certificate_updates', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['crew_certificate_id']);

            // Modify the column to be nullable
            $table->unsignedBigInteger('crew_certificate_id')->nullable()->change();

            // Re-add the foreign key constraint
            $table->foreign('crew_certificate_id')
                ->references('id')
                ->on('crew_certificates')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crew_certificate_updates', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['crew_certificate_id']);

            // Make the column not nullable again
            $table->unsignedBigInteger('crew_certificate_id')->nullable(false)->change();

            // Re-add the original foreign key constraint with cascade
            $table->foreign('crew_certificate_id')
                ->references('id')
                ->on('crew_certificates')
                ->onDelete('cascade');
        });
    }
};
