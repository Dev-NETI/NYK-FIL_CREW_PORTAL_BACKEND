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
            // Drop foreign key constraint first (it was renamed from schools to universities)
            $table->dropForeign('schools_city_id_foreign');
            // Remove the specified columns
            $table->dropColumn(['type', 'address', 'city_id']);
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
            $table->text('address')->nullable();
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null');
        });
    }
};
