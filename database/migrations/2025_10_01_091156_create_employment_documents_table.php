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
        Schema::create('employment_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crew_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('employment_document_type_id')->constrained('employment_document_types')->onDelete('cascade');
            $table->text('document_number')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employment_documents');
    }
};
