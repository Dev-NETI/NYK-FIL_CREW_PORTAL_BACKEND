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
        Schema::table('allotees', function (Blueprint $table) {
            // Drop columns that are being removed
            $table->dropColumn(['id_type', 'id_number', 'is_emergency_contact', 'is_beneficiary', 'beneficiary_percentage']);
            
            // Drop foreign key constraint and address_id column
            $table->dropForeign(['address_id']);
            $table->dropColumn('address_id');
            
            // Add new address text column
            $table->text('address')->nullable()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allotees', function (Blueprint $table) {
            // Drop the new address column
            $table->dropColumn('address');
            
            // Re-add the address_id column and foreign key
            $table->foreignId('address_id')->nullable()->constrained('addresses')->onDelete('set null');
            
            // Re-add the dropped columns
            $table->string('id_type')->nullable();
            $table->string('id_number')->nullable();
            $table->boolean('is_emergency_contact')->default(false);
            $table->boolean('is_beneficiary')->default(false);
            $table->decimal('beneficiary_percentage', 5, 2)->nullable();
        });
    }
};
