<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('rank_id')->nullable()->after('vessel_id')->constrained('ranks')->nullOnDelete();
            $table->string('port_of_departure')->nullable()->after('arrival_date');
            $table->string('port_of_arrival')->nullable()->after('port_of_departure');
            $table->decimal('basic_wage', 10, 2)->nullable()->after('port_of_arrival');
            $table->decimal('fixed_overtime', 10, 2)->nullable()->after('basic_wage');
            $table->decimal('leave_pay', 10, 2)->nullable()->after('fixed_overtime');
            $table->decimal('subsistence_allowance', 10, 2)->nullable()->after('leave_pay');
            $table->decimal('vacation_leave_conversion', 10, 2)->nullable()->after('subsistence_allowance');
            $table->decimal('total_guaranteed_monthly', 10, 2)->nullable()->after('vacation_leave_conversion');
            $table->string('currency', 10)->nullable()->default('USD')->after('total_guaranteed_monthly');
            $table->string('contract_status')->nullable()->after('currency');
            $table->text('remarks')->nullable()->after('contract_status');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['rank_id']);
            $table->dropColumn([
                'rank_id',
                'port_of_departure',
                'port_of_arrival',
                'basic_wage',
                'fixed_overtime',
                'leave_pay',
                'subsistence_allowance',
                'vacation_leave_conversion',
                'total_guaranteed_monthly',
                'currency',
                'contract_status',
                'remarks',
            ]);
        });
    }
};
