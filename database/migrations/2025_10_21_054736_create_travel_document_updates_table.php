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
        Schema::create('travel_document_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_document_id')
                ->constrained('travel_documents')
                ->onDelete('cascade');

            $table->string('crew_id')->nullable();
            $table->foreign('crew_id')
                ->references('crew_id')
                ->on('user_profiles')
                ->onDelete('cascade');

            $table->json('original_data');
            $table->json('updated_data');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
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
        Schema::dropIfExists('travel_document_updates');
    }
};
