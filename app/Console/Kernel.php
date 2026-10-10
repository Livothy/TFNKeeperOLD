<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule) {
        $schedule->command('check-news')
            ->everyMinute();
        $schedule->command('check-sales')
            ->everyMinute();
        $schedule->command('check-raids')
            ->everyMinute();
        $schedule->exec('rm public/images/avatars/*.tmp')
            ->daily();
        $schedule->command('update-extension-tracker')
            ->daily();
        $schedule->command('update-staff-reward-actions')
            ->daily();
        $schedule->command('index-search-pages')
            ->daily();
        $schedule->command('clear-week-old-user-ads')
            ->daily();
        $schedule->command('reward-ranks')
            ->daily();
        $schedule->command('reset-hol')
            ->daily();

        $schedule->command('update-timed-daily')
            ->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands() {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
