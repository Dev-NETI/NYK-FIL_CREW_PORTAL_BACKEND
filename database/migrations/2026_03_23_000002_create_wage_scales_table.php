<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wage_scales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rank_id')->nullable()->constrained('ranks')->nullOnDelete();
            $table->foreignId('vessel_type_id')->nullable()->constrained('vessel_types')->nullOnDelete();
            $table->date('effective_date');
            $table->decimal('basic_wage', 10, 2);
            $table->decimal('fixed_overtime', 10, 2)->default(0);
            $table->decimal('leave_pay', 10, 2)->default(0);
            $table->decimal('subsistence_allowance', 10, 2)->default(0);
            $table->decimal('vacation_leave_conversion', 10, 2)->default(0);
            $table->decimal('total_guaranteed_monthly', 10, 2);
            $table->string('currency', 10)->default('USD');
            $table->string('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['rank_id', 'vessel_type_id', 'effective_date'], 'wage_scales_rank_vessel_type_date_unique');
            $table->index(['rank_id', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wage_scales');
    }
};
