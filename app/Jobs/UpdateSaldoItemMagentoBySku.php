<?php

namespace App\Jobs;

use App\Http\Controllers\Magento\MagentoController;
use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateSaldoItemMagentoBySku extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $sessaoMagento;
    protected $itemDatasul;
    protected $itemMagento;
    protected $storeViewMagento;
    protected $tipoConsskuMagento;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($sessaoMagento,$itemDatasul,$itemMagento,$storeViewMagento,$tipoConsskuMagento)
    {
        $this->sessaoMagento = $sessaoMagento;
        $this->itemDatasul = $itemDatasul;
        $this->itemMagento = $itemMagento;
        $this->storeViewMagento = $storeViewMagento;
        $this->tipoConsskuMagento = $tipoConsskuMagento;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $objMagento = new MagentoController();
        $objMagento->updateItemMagentoBySKU($this->sessaoMagento,$this->itemDatasul,$this->itemMagento, $this->storeViewMagento, $this->tipoConsskuMagento);

    }
}
