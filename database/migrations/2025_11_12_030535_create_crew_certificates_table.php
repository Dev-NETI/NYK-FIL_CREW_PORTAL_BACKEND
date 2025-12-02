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
        Schema::create('crew_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certificate_id')->constrained('certificates')->onDelete('cascade');
            $table->string('crew_id')->nullable();
            $table->foreign('crew_id')->references('crew_id')->on('user_profiles')->onDelete('cascade');
            $table->text('grade')->comment('for coc')->nullable();
            $table->text('rank_permitted')->comment('for jiss')->nullable();
            $table->text('certificate_no')->comment('certificate/license no.')->nullable();
            $table->text('issued_by')->comment('issuing authority / training center')->nullable();
            $table->date('date_issued')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_ext')->nullable();
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
        Schema::dropIfExists('crew_certificates');
    }
};
