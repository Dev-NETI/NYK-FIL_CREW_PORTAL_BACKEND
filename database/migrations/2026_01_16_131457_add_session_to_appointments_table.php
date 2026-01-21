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
        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('session', ['AM', 'PM'])->nullable()->after('date');
            $table->time('time')->nullable()->change();
        });

        DB::statement("
            UPDATE appointments
            SET session = CASE
                WHEN time IS NULL THEN NULL
                WHEN TIME(time) < '12:00:00' THEN 'AM'
                ELSE 'PM'
            END
            WHERE session IS NULL
        ");

        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['department_id', 'date', 'session']);
            $table->index(['user_id', 'date', 'session']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['department_id', 'date', 'session']);
            $table->dropIndex(['user_id', 'date', 'session']);

            $table->index(['department_id', 'date', 'time']);
            $table->index(['user_id', 'date', 'time']);

            $table->dropColumn('session');

            // revert to not-null time (only safe if you don't have null time rows)
            $table->time('time')->nullable(false)->change();
        });
    }
};
