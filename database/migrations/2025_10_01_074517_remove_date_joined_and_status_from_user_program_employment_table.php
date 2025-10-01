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
        Schema::table('user_program_employment', function (Blueprint $table) {
            $table->dropColumn(['date_joined', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_program_employment', function (Blueprint $table) {
            $table->date('date_joined')->nullable();
            $table->enum('status', ['Active', 'Completed', 'On Hold', 'Terminated'])->default('Active');
        });
    }
};
