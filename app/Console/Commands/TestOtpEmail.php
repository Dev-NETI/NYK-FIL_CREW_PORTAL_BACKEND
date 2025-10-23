<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

class TestOtpEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test-otp {email} {--name=User}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test OTP email to verify email configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $name = $this->option('name');

        // Generate a test OTP
        $testOtp = sprintf('%06d', random_int(0, 999999));

        $this->info("Sending test OTP email to: {$email}");
        $this->info("OTP Code: {$testOtp}");

        try {
            Mail::to($email)->send(new OtpMail($testOtp, $name, 10));

            $this->info("✓ Email sent successfully!");
            $this->info("Check your inbox at: {$email}");

            return 0;
        } catch (\Exception $e) {
            $this->error("✗ Failed to send email:");
            $this->error($e->getMessage());

            return 1;
        }
    }
}
