<?php

namespace App\Console;

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
        // Commands\Inspire::class,
        Commands\PedidosIntegra::class,
        Commands\EnviaRastreio::class,
        Commands\StatusEntregue::class,
        Commands\StatusFaturado::class,
        Commands\UpdateSaldo::class
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

        $schedule->command('command:pedidosintegra')->everyMinute();



        /*
        $schedule->call( function(){
            Log::info('log', ['message' => 'Schedule pedidosdatasulintegra']);
            return redirect('/pedidosdatasulintegra');
        })->everyMinute();
        */
    }
}
