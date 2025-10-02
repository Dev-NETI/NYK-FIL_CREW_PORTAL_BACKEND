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
        Schema::create('certificate_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crew_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('certificate_document_type_id')->constrained('certificate_document_types')->onDelete('cascade');
            $table->string('certificate')->comment('certificate or grade')->nullable();
            $table->string('certificate_no')->comment('certificate_no or license_no')->nullable();
            $table->string('issuing_authority')->comment('issuing_authority or training_center')->nullable();
            $table->date('date_issued')->nullable();
            $table->date('expiry_date')->nullable();
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
        Schema::dropIfExists('certificate_documents');
    }
};
