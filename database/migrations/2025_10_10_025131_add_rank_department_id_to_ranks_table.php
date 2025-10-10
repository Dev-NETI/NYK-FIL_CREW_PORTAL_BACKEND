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
        Schema::table('ranks', function (Blueprint $table) {
            $table->unsignedBigInteger('rank_department_id')->after('name')->nullable();
            // $table->foreignId('rank_department_id')->after('name')->constrained('rank_departments')->default(1);
            $table->foreign('rank_department_id')->references('id')->on('rank_departments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ranks', function (Blueprint $table) {
            $table->dropForeign(['rank_department_id']);
            $table->dropColumn('rank_department_id');
        });
    }
};
