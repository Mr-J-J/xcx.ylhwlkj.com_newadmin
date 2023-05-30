<?php

namespace App\Console;

// use App\Console\Commands\SyncApiData;
use App\Jobs\SyncCities;
use App\Jobs\SyncMovies;
use App\Jobs\SyncCinemas;
use App\Jobs\SyncSchedules;
use App\Jobs\SyncCurrentMovies;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [

    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        // city cinema hot rightnow movies schedules
        // $schedule->command('SyncCommand city --auto')->dailyAt('1:00')->withoutOverlapping(2);
        // $schedule->command('SyncCommand cinema --auto')->dailyAt('1:00')->withoutOverlapping(2);
        // $schedule->command('SyncCommand hot --auto')->dailyAt('1:00')->withoutOverlapping(2);
        // $schedule->command('SyncCommand rightnow --auto')->dailyAt('1:00')->withoutOverlapping(2);
        // $schedule->command('SyncCommand movies --auto')->dailyAt('1:00')->withoutOverlapping(2);
        // $schedule->command('SyncCommand schedules --auto')->dailyAt('1:00')->withoutOverlapping(2);
        // $schedule->command('syncwpdata city --auto')->dailyAt('1:00')->withoutOverlapping(2);
        // $schedule->command('syncwpdata cinema --auto')->dailyAt('1:00')->withoutOverlapping(2);
        // $schedule->command('syncwpdata movies --auto')->dailyAt('1:00')->withoutOverlapping(2);
        $schedule->command('dataclear')->dailyAt('21:35')->withoutOverlapping(2);
        $schedule->command('apidatadown hot')->dailyAt('02:20')->withoutOverlapping(2);
        //$schedule->command('apidatadown schedule')->dailyAt('02:40')->withoutOverlapping(2);
        $schedule->command('ordertimer')->everyMinute();

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
