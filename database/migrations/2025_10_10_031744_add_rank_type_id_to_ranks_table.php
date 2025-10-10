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
            $table->unsignedBigInteger('rank_type_id')->after('rank_department_id')->nullable();
            $table->foreign('rank_type_id')->references('id')->on('rank_types');
            // $table->foreignId('rank_type_id')->after('rank_department_id')->constrained('rank_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ranks', function (Blueprint $table) {
            $table->dropForeign(['rank_type_id']);
            $table->dropColumn('rank_type_id');
        });
    }
};
