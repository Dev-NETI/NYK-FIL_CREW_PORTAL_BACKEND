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
        Schema::create('user_education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('graduated_school_id')->nullable()->constrained('universities')->onDelete('set null');
            $table->date('date_graduated')->nullable();
            $table->string('degree')->nullable();
            $table->string('field_of_study')->nullable();
            $table->decimal('gpa', 3, 2)->nullable();
            $table->enum('education_level', ['high_school', 'vocational', 'bachelor', 'master', 'doctorate', 'other'])->nullable();
            $table->text('certifications')->nullable();
            $table->text('additional_training')->nullable();
            
            // Audit fields
            $table->foreignId('modified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('user_id');
            $table->index('graduated_school_id');
            $table->index('education_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_education');
    }
};
