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
        // Close expired pools every minute
        $schedule->command('squares:close-expired-pools')->everyMinute();

        // Assign numbers for scheduled Type B pools every minute
        $schedule->command('squares:assign-scheduled-numbers')->everyMinute();

        // Update game status to 'started' when game datetime is reached
        $schedule->command('squares:update-game-status')->everyMinute();
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
