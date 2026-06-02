<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
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
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('slots:clear-expired-reservations')->everyMinute();

        // Сессии в MySQL: удаление просроченных строк (дополнительно к встроенной lottery-GC)
        $schedule->call(function () {
            if (config('session.driver') !== 'database') {
                return;
            }
            $lifetimeSeconds = (int) config('session.lifetime', 120) * 60;
            $cutoff = time() - $lifetimeSeconds;
            DB::table(config('session.table', 'sessions'))->where('last_activity', '<', $cutoff)->delete();
        })->hourly();
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
