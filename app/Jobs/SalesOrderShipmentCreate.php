<?php

namespace App\Jobs;

use App\Http\Controllers\Magento\MagentoController;
use App\Jobs\Job;
use App\Models\ItemExped;
use App\Models\Rastreamento;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SalesOrderShipmentCreate extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $sessaoMagento;
    protected $nrPedidoMagentoIntegrado;
    protected $itensExpedidos;
    protected $comment;
    protected $email;
    protected $includeComment;
    protected $expedItem;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($sessaoMagento, $nrPedidoMagentoIntegrado, $itensExpedidos, $comment, $email, $includeComment, $expedItem )
    {
        $this->sessaoMagento = $sessaoMagento;
        $this->nrPedidoMagentoIntegrado = $nrPedidoMagentoIntegrado;
        $this->itensExpedidos = $itensExpedidos;
        $this->comment = $comment;
        $this->email = $email;
        $this->includeComment = $includeComment;
        $this->expedItem = $expedItem;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $objMagento = new MagentoController();

       // dd($this->itensExpedidos);

        //Criar Entrega no Magento
        $codigoEntregaMagento = $objMagento->salesOrderShipmentCreate($this->sessaoMagento, $this->nrPedidoMagentoIntegrado, $this->itensExpedidos, $this->comment, $this->email, $this->includeComment);

        if ($codigoEntregaMagento != null){

            /*
            $comDespachado = new \stdClass();
            $comDespachado->status = "complete"; //Order status
            $comDespachado->comment = "Chave de Acesso: ".$this->expedItem->expedItem->chaveXml; //comentário enviado (opcional)
            $comDespachado->notify = 0;  //Notification flag (optional)

            //Verificar funcionamento deste comentário
            $objMagento->salesOrderAddComment($this->sessaoMagento,$this->nrPedidoMagentoIntegrado,$comDespachado->status,$comDespachado->comment,$comDespachado->notify);
            */

            //Persistindo no Banco Integrador
            foreach ($this->expedItem->expedItem->Items as $item) {

                $itemExped = ItemExped::create([
                    'sku' => $item->codigoItem,
                    'qtd' => $item->qtd
                ]);

                for ($i = 0; $i < count($item->Rastremantos); $i++) {
                    $rastreamento = Rastreamento::create([
                        'cod_rastreamento' => $item->Rastremantos[$i]
                    ]);

                    if ($rastreamento != null){
                        $itemExped->rastreamentos()->save($rastreamento);

                        $rastreamento->itemexpeds()->updateExistingPivot($itemExped->id, [
                            "cod_entrega" => $codigoEntregaMagento,
                            'key_xml' => $this->expedItem->expedItem->chaveXml,
                            'sincronizado' => false,
                            'nr_ped_datasul' => $this->expedItem->expedItem->nrPedidoDatasul,
                            'nr_ped_magento' => $this->nrPedidoMagentoIntegrado
                        ]);
                    }
                }

            }

            return $this->expedItem->expedItem->nrPedidoDatasul."OK";

        } else{
            return "Erro ao gerar Entregas no Magento, verifique o Log de eventos.";
        }

    }
}
