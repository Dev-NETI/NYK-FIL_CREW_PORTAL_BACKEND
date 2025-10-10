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
        Schema::dropIfExists('certificate_documents');
        Schema::dropIfExists('certificate_document_types');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse migrations not implemented - restore from backup if needed
    }
};
