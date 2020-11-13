<?php

namespace App\Console\Commands;

use App\Http\Controllers\PedidosCargaController;
use Illuminate\Console\Command;
use Log;

class StatusFaturado extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:statusFaturado';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Altera status do pedido para Completo NFe emitida';

    protected $statusFaturado;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PedidosCargaController $statusFaturado)
    {
        parent::__construct();
        $this->statusFaturado = $statusFaturado;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info('log', ['message' => 'Schedule statusFaturado']);

        $this->statusFaturado->statusFaturado();

    }
}
