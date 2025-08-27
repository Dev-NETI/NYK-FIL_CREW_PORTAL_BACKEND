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
        Schema::table('fleets', function (Blueprint $table) {
            $table->dropColumn(['code', 'description', 'manager_name', 'manager_contact']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fleets', function (Blueprint $table) {
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->string('manager_name')->nullable();
            $table->string('manager_contact')->nullable();
        });
    }
};
