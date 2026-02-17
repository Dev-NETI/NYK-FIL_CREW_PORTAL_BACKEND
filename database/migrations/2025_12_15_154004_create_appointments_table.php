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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('appointment_type_id')->nullable()
                ->constrained('appointment_types')
                ->nullOnDelete();

            $table->foreignId('schedule_id')->nullable()
                ->constrained('department_schedules')
                ->nullOnDelete();

            $table->date('date');
            $table->time('time');

            $table->text('purpose')->nullable();
            
            $table->integer('duration_minutes')->default(30);

            $table->enum('status', ['pending','confirmed','cancelled','completed'])
                ->default('confirmed');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('created_by_type', ['crew','department'])->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['department_id','date','time']);
            $table->index(['user_id','date','time']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
