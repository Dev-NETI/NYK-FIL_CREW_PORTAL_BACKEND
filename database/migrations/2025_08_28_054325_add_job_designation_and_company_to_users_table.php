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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('job_designation_id')->nullable()->after('rank_id');
            $table->unsignedBigInteger('company_id')->nullable()->after('job_designation_id');
            
            $table->foreign('job_designation_id')->references('id')->on('job_designations')->onDelete('set null');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['job_designation_id']);
            $table->dropForeign(['company_id']);
            $table->dropColumn(['job_designation_id', 'company_id']);
        });
    }
};
