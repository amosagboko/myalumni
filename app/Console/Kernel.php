<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * These cron jobs are run in the background by a cron service.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // ... existing scheduled tasks ...
        $schedule->command('content:cleanup')->daily();
        $schedule->command('chat:cleanup')->daily(); // Clean up old chat messages daily
        $schedule->command('eoi:sync-candidate-payments')->everyTwoMinutes();

        $schedule->command('backup:clean')->daily()->at('01:00');
        $schedule->command('backup:run')->daily()->at('01:30');
        $schedule->command('backup:monitor')->daily()->at('03:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');

        $this->commands = [
            Commands\ResetElectionData::class,
            Commands\SimulateVoting::class,
            Commands\ListElections::class,
            Commands\ExportDatabase::class,
            Commands\ImportDatabase::class,
            \App\Console\Commands\SyncEoiCandidatePayments::class,
            \App\Console\Commands\CleanupOldChats::class,
            \App\Console\Commands\TestChatCleanup::class,
            \App\Console\Commands\TestContentCleanup::class,
        ];
    }
} 