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
        Schema::create('travel_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crew_id')->constrained('users')->onDelete('cascade');
            $table->string('id_no')->nullable();
            $table->foreignId('travel_document_type_id')->constrained('travel_document_types')->onDelete('cascade');
            $table->text('place_of_issue')->nullable();
            $table->date('date_of_issue')->nullable();
            $table->date('expiration_date')->nullable();
            $table->integer('remaining_pages')->nullable();
            $table->string('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_documents');
    }
};
