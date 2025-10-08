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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certificate_type_id')->constrained('certificate_types')->onDelete('cascade');
            $table->string('regulation')->nullable()->comment('for STCW - CertType 1, Government - CertType 2');
            $table->string('name')->nullable();
            $table->enum('stcw_type', ['COC', 'COP'])->nullable();
            $table->string('code')->nullable();
            $table->string('vessel_type')->nullable()->comment('for NMC Courses - CertType 3');
            $table->enum('nmc_type', ['NMC', 'NMCR'])->nullable()->comment('for NMC Courses - CertType 3');
            $table->enum('nmc_department', ['Deck', 'Engine', 'Catering', 'Common'])->nullable()->comment('for NMC Courses - CertType 3');
            $table->string('rank')->nullable()->comment('for TESDA - CertType 4');
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
        Schema::dropIfExists('certificates');
    }
};
