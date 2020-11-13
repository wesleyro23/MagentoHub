<?php

namespace App\Console\Commands;

use App\Http\Controllers\ItensCargaController;
use Illuminate\Console\Command;
use Log;

class UpdateSaldo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:integracaoSaldo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza saldo dos itens no Magento';

    protected $integracaoSaldo;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ItensCargaController $integracaoSaldo)
    {
        parent::__construct();
        $this->integracaoSaldo = $integracaoSaldo;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info('log', ['message' => 'Schedule integracaoSaldo']);

        $this->integracaoSaldo->integracaoSaldo();

    }
}
