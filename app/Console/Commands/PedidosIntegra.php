<?php

namespace App\Console\Commands;

use Log;
use App\Http\Controllers\PedidosCargaController;
use Illuminate\Console\Command;

class PedidosIntegra extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:pedidosintegra';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Busca os pedidos no Magento';

    protected $pedidosIntegra;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PedidosCargaController $pedidosIntegra)
    {
        parent::__construct();
        $this->pedidosIntegra = $pedidosIntegra;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info('log', ['message' => 'Schedule pedidosintegra']);

        $this->pedidosIntegra->integracao();


        /*
         * Usando
         * https://www.youtube.com/watch?v=2XZ8HKgvhGU
         * Adicionar a Classe em Console\Kernel.php
         * 
         * Colocar um bat agentado para rodar
         * php artisan command:pedidosintegra
         * */

    }
}
