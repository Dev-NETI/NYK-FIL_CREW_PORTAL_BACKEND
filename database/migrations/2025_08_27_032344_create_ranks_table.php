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
        Schema::create('ranks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rank_group_id')->constrained('rank_groups')->onDelete('restrict');
            $table->string('name'); // Captain, Chief Officer, AB, etc.
            $table->string('code', 10)->unique(); // CAPT, C/O, AB, etc.
            $table->string('description')->nullable();
            $table->integer('hierarchy_level')->default(0); // For ranking order
            $table->boolean('is_officer')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ranks');
    }
};
