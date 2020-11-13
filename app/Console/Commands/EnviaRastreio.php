<?php

namespace App\Console\Commands;

use App\Http\Controllers\ExpedItemController;
use App\Http\Controllers\TransportadorasController;
use Illuminate\Console\Command;
use Log;

class EnviaRastreio extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:enviaRastreio';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia o rastreio para a entrega no magento';

    protected $enviaRastreio;
    protected $enviaRastreioTransp;
    protected $buscaRastreioTransportador;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ExpedItemController $enviaRastreio, TransportadorasController $enviaRastreioTransp, TransportadorasController $buscaRastreioTransportador)
    {
        parent::__construct();
        $this->enviaRastreio = $enviaRastreio;
        $this->enviaRastreioTransp = $enviaRastreioTransp;
        $this->buscaRastreioTransportador = $buscaRastreioTransportador;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info('log', ['message' => 'Schedule enviaRastreio']);
        
        $this->enviaRastreio->getEnviaRastreio();
        $this->enviaRastreioTransp->getEnviaRastreioTransp();
        $this->buscaRastreioTransportador->getBuscaRastreioTransportador();

        /*
         * Usando
         * https://www.youtube.com/watch?v=2XZ8HKgvhGU
         * Adicionar a Classe em Console\Kernel.php
         *
         * Colocar um bat agentado para rodar
         * php artisan command:enviaRastreio
         * */
    }
}
