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
        // Step 1: Drop rank_group_id from ranks table
        Schema::table('ranks', function (Blueprint $table) {
            $table->dropForeign(['rank_group_id']);
            $table->dropColumn('rank_group_id');
        });

        // Step 2: Drop rank_category_id from rank_groups table
        Schema::table('rank_groups', function (Blueprint $table) {
            $table->dropForeign(['rank_category_id']);
            $table->dropColumn('rank_category_id');
        });

        // Step 3: Drop rank_groups table
        Schema::dropIfExists('rank_groups');

        // Step 4: Drop rank_categories table
        Schema::dropIfExists('rank_categories');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate rank_categories table
        Schema::create('rank_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Recreate rank_groups table
        Schema::create('rank_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rank_category_id')->constrained('rank_categories');
            $table->string('name');
            $table->string('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Add rank_group_id back to ranks table
        Schema::table('ranks', function (Blueprint $table) {
            $table->foreignId('rank_group_id')->after('id')->constrained('rank_groups');
        });
    }
};
