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
        $schedule->command('reservations:update-status')->hourly();
        
        $schedule->call(function () {
            // Only run if not already processing
            if (Cache::lock('processing_waiting_list', 60)->get()) {
                try {
                    app(ReservationController::class)->processWaitingList();
                } finally {
                    Cache::lock('processing_waiting_list')->release();
                }
            }
        })->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

protected $routeMiddleware = [
    'checkRole' => \App\Http\Middleware\CheckStaffRole::class,
    ];
    
}

