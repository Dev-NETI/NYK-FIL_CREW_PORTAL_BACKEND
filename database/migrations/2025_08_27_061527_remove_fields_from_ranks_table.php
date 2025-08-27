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
            $table->dropColumn(['code', 'description', 'hierarchy_level', 'is_officer']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ranks', function (Blueprint $table) {
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->integer('hierarchy_level')->nullable();
            $table->boolean('is_officer')->default(false);
        });
    }
};
