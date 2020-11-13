<?php

namespace App\Console\Commands;

use App\Http\Controllers\ExpedItemController;
use App\Http\Controllers\TransportadorasController;
use Illuminate\Console\Command;
use Log;

class StatusEntregue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:statusEntregue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Altera o status do pedido para entregue';
    
    protected $statusEntrega;
    protected $statusEntregaTransp;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ExpedItemController $statusEntrega, TransportadorasController $statusEntregaTransp)
    {
        parent::__construct();
        $this->statusEntrega = $statusEntrega;
        $this->statusEntregaTransp = $statusEntregaTransp;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info('log', ['message' => 'Schedule statusEntregue']);
        
        $this->statusEntrega->getEntrega();
        $this->statusEntregaTransp->getEntregaTransportador();
    }
}
