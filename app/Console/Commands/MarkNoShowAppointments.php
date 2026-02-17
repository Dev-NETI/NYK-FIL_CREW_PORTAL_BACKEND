<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MarkNoShowAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:mark-no-show';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark confirmed appointments as no_show after appointment time passes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $graceMinutes = 15;

        Appointment::query()
            ->where('status', 'confirmed')
            ->whereRaw("TIMESTAMP(date, time) < (NOW() - INTERVAL ? MINUTE)", [$graceMinutes])
            ->update([
                'status' => 'no_show',
            ]);

        $this->info('No-show marking completed.');
        return self::SUCCESS;
    }
}
