<?php

namespace App\Console;

use App\Console\Commands\GenerateWeeklyReport;
use App\Console\Commands\RegisterFreeKeywordRankingJobsCommand;
use App\Console\Commands\RegisterHeatmapRadiusZipcodeRanking;
use App\Console\Commands\RegisterPaidKeywordRankingJobsCommand;
use App\Console\Commands\UpdateDailyAverageRanking;
use App\Console\Commands\UpdateWeeklyAverageRanking;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(RegisterPaidKeywordRankingJobsCommand::class)->everyFiveMinutes();
        $schedule->command(RegisterFreeKeywordRankingJobsCommand::class)->hourly();
        $schedule->command(UpdateWeeklyAverageRanking::class)->timezone('UTC')->at('2:00');
        $schedule->command(UpdateWeeklyAverageRanking::class)->timezone('UTC')->at('14:00');
        $schedule->command(UpdateDailyAverageRanking::class)->everyThirtyMinutes();
        $schedule->command(UpdateDailyAverageRanking::class)->daily();
        $schedule->command(RegisterHeatmapRadiusZipcodeRanking::class)->everySixHours();
//        $schedule->command(GenerateWeeklyReport::class)->fridays();
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
    }
}
