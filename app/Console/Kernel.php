<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('duration_sleep')->hourly();

        // Get times from environment variables with defaults if not set
        $sleepTime = env('FIXED_SLEEP_TIME', '22:00');
        $activateTime = env('FIXED_ACTIVATE_TIME', '08:00');
        
        // Run at sleep time (default 11:00 PM) UTC+7
        $schedule->command('fixed_sleep')
            ->timezone('Asia/Bangkok')
            ->dailyAt($sleepTime);
        
        // Run at activate time (default 8:00 AM) UTC+7
        $schedule->command('fixed_sleep')
            ->timezone('Asia/Bangkok')
            ->dailyAt($activateTime);
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
