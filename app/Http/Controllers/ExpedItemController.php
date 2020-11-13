<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Magento\MagentoController;
use App\Jobs\SalesOrderShipmentCreate;
use App\Models\ExpedItem;
use App\Models\ItemExped;
use App\Models\Magento\AssociativeEntity;
use App\Models\Parametro;
use App\Models\Rastreamento;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Log;

class ExpedItemController extends Controller
{
    protected $request;
    protected $objMagento;
    protected $parametros;

    public function __construct(Request $request, MagentoController $objMagento, Parametro $parametros)
    {
        $this->request = $request;
        $this->objMagento = $objMagento;
        $this->parametros = $parametros;
    }
    
    public function getEnviaItens()
    {

        $parametros = $this->parametros->all();

        //Define parametros
        foreach ($parametros as $parametro){
            $loginMagento = $parametro->login_magento;
            $senhaMagento = $parametro->senha_magento;
        }

        //comentário enviado (opcional)
        $comment = "Enviando encomenda ao Transportador";
        //Envio de Email (opcional)
        $email = 0;
        //Incluir Comentário no Email (opcional)
        $includeComment = 1;


        //Recupera a sessao Magento
        $sessaoMagento = $this->objMagento->getSessao($loginMagento,$senhaMagento);

        $dados = $this->request->get('expedItemJson');

        //Exporta valor recebido pelo datasul para storege/logs
        date_default_timezone_set('America/Sao_Paulo');
        $date = date('dmYHisU');
        file_put_contents(storage_path()."/logs/$date.log", $dados);

        $expedItem = json_decode($dados);

//        dd($expedItem);

        return $this->criarEntregaMagento($sessaoMagento, $expedItem, $comment, $email, $includeComment);

    }

    public function criarEntregaMagento($sessaoMagento, $expedItem, $comment, $email, $includeComment)
    {

        //localizar Pedido Magento no integrador
        $request_nrPedMagento = DB::table('pedidos')
            ->select('numero_pedido_magento')
            ->where('numero_pedido_datasul', $expedItem->expedItem->nrPedidoDatasul)->get();

        $nrPedidoMagentoIntegrado = $request_nrPedMagento[0]->numero_pedido_magento;

//        dd($nrPedidoMagentoIntegrado);

        //Localizando Pedido no MAGENTO
        $pedidoMagento = $this->objMagento->getSalesOrderInfo($sessaoMagento, $nrPedidoMagentoIntegrado);

//        dd($pedidoMagento);

        //Lista de itens a serem enviados
        $itensExpedidos = array();

        $contador = 0;

        // Localizando IDs do item no pedido Magento e montando OrderItemIdQty[] para o Magento
        foreach ($expedItem->expedItem->Items as $item) {

            foreach ($pedidoMagento->items as $itemMagento) {

                if (strtoupper($itemMagento->sku) === strtoupper($item->codigoItem)){

                    $orderItem = new \stdClass();
                    $orderItem->order_item_id = $itemMagento->item_id;
                    $orderItem->qty = $item->qtd;
                    $itensExpedidos[$contador] = $orderItem;
                }
            }
            $contador++;
        }

        /********************************************************************************************/

        Log::info('log', ['message' => "Criando entrega no Magento..."]);

        //Verifica se já existe uma entrega para oa pedido
        $getEntregaMagento = $this->getEntregaMagento($sessaoMagento,$nrPedidoMagentoIntegrado);

//        dd($getEntregaMagento);

        if($getEntregaMagento == ""){

            $job = $this->dispatch(
                new SalesOrderShipmentCreate($sessaoMagento, $nrPedidoMagentoIntegrado, $itensExpedidos, $comment, $email, $includeComment, $expedItem)
            );

            if ($job != null){
                return $expedItem->expedItem->nrPedidoDatasul."OK";
            } else{
                return "Erro ao criar job para entrega!!!";
            }

        } else{

            $consultaCodEntregaIntegrador = DB::table('rastreamentos_itemexpeds')
                ->where('cod_entrega', $getEntregaMagento)->get();

            if ($consultaCodEntregaIntegrador == null){

                foreach ($expedItem->expedItem->Items as $item) {

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
                                "cod_entrega" => $getEntregaMagento,
                                'key_xml' => $expedItem->expedItem->chaveXml,
                                'sincronizado' => false,
                                'nr_ped_datasul' => $expedItem->expedItem->nrPedidoDatasul,
                                'nr_ped_magento' => $nrPedidoMagentoIntegrado
                            ]);
                        }
                    }

                }

                return $expedItem->expedItem->nrPedidoDatasul."OK";

            } else{
                return $expedItem->expedItem->nrPedidoDatasul."OK";
            }

        }

    }

    public function getEntregaMagento($sessaoMagento,$nrPedidoMagentoIntegrado)
    {
        $pedMagento = $this->objMagento->getSalesOrderInfo($sessaoMagento,$nrPedidoMagentoIntegrado);

        //Filtro para verificar entrega
        $filtro = new \stdClass();
        $ae = new AssociativeEntity();
        $ae->setKey('order_id');
        $ae->setValue($pedMagento->order_id);
        $filtro->filter[] = $ae;

        $listaEntregaMagento = $this->objMagento->salesOrderShipmentList($sessaoMagento, $filtro);

        //dd($listaEntregaMagento);

        if ($listaEntregaMagento == null){
            $codEntregaMagento = "";
        } else{
            $codEntregaMagento = end($listaEntregaMagento)->increment_id;
        }

        return $codEntregaMagento;

    }

    public function getEnviaRastreio()
    {
        $parametros = $this->parametros->all();

        //Define parametros
        foreach ($parametros as $parametro){
            $loginMagento = $parametro->login_magento;
            $senhaMagento = $parametro->senha_magento;
            $codigoTransportador = $parametro->codigo_transportador;
        }

        //Recupera a sessao Magento
        $sessaoMagento = $this->objMagento->getSessao($loginMagento,$senhaMagento);

        $requestTrack = $this->enviaTrackMagento($sessaoMagento, $codigoTransportador);

        if ($requestTrack == "OK"){
            return redirect("/");
        }

	}

    public function enviaTrackMagento($sessaoMagento, $codigoTransportador)
    {

        //buscando lista de itens sem sincronizar
		$listaItensPendentesTrack = DB::table('rastreamentos_itemexpeds')
            ->where([
                ['sincronizado', '=', false],
                ['cod_entrega', '<>', '']
            ])->get();

//		dd($listaItensPendentesTrack);

        $contador = 1;

        foreach ($listaItensPendentesTrack as $item) {

            $rastreamento = Rastreamento::find($item->id_rastreamento);

//            dd($rastreamento->cod_rastreamento);

            $addTrackEntregaMagento = $this->objMagento->salesOrderShipmentAddTrack($sessaoMagento, $item->cod_entrega, $codigoTransportador, "Vol: ".$contador, $rastreamento->cod_rastreamento);

            if ($addTrackEntregaMagento != false){

                $enviaEmail = $this->objMagento->salesOrderShipmentSendInfo($sessaoMagento, $item->cod_entrega, $item->key_xml);

                if ($enviaEmail){
                    DB::table('rastreamentos_itemexpeds')
                        ->where('id', '=', $item->id)
                        ->update([
                            'sincronizado' => true,
                            'email_enviado' => true
                        ]);

                    //localizar Pedido no integrador
                    $numPedidoDatasul = DB::table('rastreamentos_itemexpeds')
                        ->select('nr_ped_datasul')
                        ->where('id', $item->id)->get();

                    DB::table('pedidos')
                        ->where('numero_pedido_datasul', '=', $numPedidoDatasul[0]->nr_ped_datasul)
                        ->update([
                            'sincronizado_magento' => true
                        ]);
                }
            }

            $contador++;

        }

        return "OK";

    }

    public function getEntrega()
    {
        //buscando lista de itens sem sincronizar
        $listaItensPendentesTrack = DB::table('rastreamentos_itemexpeds')
            ->select('rastreamentos_itemexpeds.cod_entrega')
            ->where([
                ['sincronizado', '=', true],
                ['email_enviado', '=', true],
                ['cod_entrega', '<>', ''],
                ['entrega_correios', '=', false]
            ])
            ->distinct()
            ->get();

//        dd($listaItensPendentesTrack);

        foreach ($listaItensPendentesTrack as $codentrega) {

            $listaRastreios = DB::table('rastreamentos_itemexpeds')
                ->join('rastreamentos','rastreamentos_itemexpeds.id_rastreamento', '=', 'rastreamentos.id')
                ->select('rastreamentos_itemexpeds.nr_ped_magento', 'rastreamentos_itemexpeds.cod_entrega', 'rastreamentos.cod_rastreamento')
                ->where([
                    ['cod_entrega', '=', $codentrega->cod_entrega]
                ])
                ->get();

//            dd($listaRastreios);
//            dd($codentrega->cod_entrega);

            //for buscando numero dos rastreios
            for ($i = 0; $i < count($listaRastreios); $i++){

                //Passo o restreio para saber a situação da entrega
                $retornoEntrega = $this->rastrear($listaRastreios[$i]->cod_rastreamento);

//                dd($retornoEntrega);

                if ($retornoEntrega){

                    $parametros = $this->parametros->all();

                    //Define parametros
                    foreach ($parametros as $parametro){
                        $loginMagento = $parametro->login_magento;
                        $senhaMagento = $parametro->senha_magento;
                    }

                    //comentário enviado (opcional)
                    $comment = "Entrega Efetuada...";
                    //Status do Pediso
                    $status = 'entregue';
                    //Envio de Email (opcional)
                    $notify = true;

                    //Recupera a sessao Magento
                    $sessaoMagento = $this->objMagento->getSessao($loginMagento,$senhaMagento);

                    //Altera status do pedido para entregue
                    $entregaEfetuada = $this->objMagento->salesOrderAddComment($sessaoMagento,$listaRastreios[0]->nr_ped_magento,$status,$comment,$notify);

                    //Altera status no integrador para entregue
                    if ($entregaEfetuada){

                        DB::table('rastreamentos_itemexpeds')
                            ->where('cod_entrega', '=', $codentrega->cod_entrega)
                            ->update(['entrega_correios' => 1]);
                    }

                }

            }


        }

    }

    public function rastrear($codigo)
    {

        $post = array('Objetos' => $codigo);
        // iniciar CURL
        $ch = curl_init();
        // informar URL e outras funções ao CURL
        curl_setopt($ch, CURLOPT_URL, "https://www2.correios.com.br/sistemas/rastreamento/resultado_semcontent.cfm");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($post));
        // Acessar a URL e retornar a saída
        $output = curl_exec($ch);
        // liberar
        curl_close($ch);
        // Imprimir a saída
        //dd($output);

        // Create phpQuery document with returned HTML
        $doc = \phpQuery::newDocument($output);

        $rastreamento = array();

        foreach($doc['table tr:nth-child(1) td'] as $table){
            $rastreamento[] = pq($table)->find('strong')->html();
        }

        for ($i = 0; $i < count($rastreamento); $i++){

            if ($rastreamento[$i] == 'Objeto entregue ao destinatário' ){
                return true;
            }

        }




        /*
        $curl = new Curl();
        $html = $curl->simple('http://websro.correios.com.br/sro_bin/txect01$.QueryList?P_LINGUA=001&P_TIPO=001&P_COD_UNI='.$codigo);
        \phpQuery::newDocumentHTML($html, $charset = 'utf-8');

        dd($html);

        $rastreamento = array();

        $c = 0;

        foreach(\phpQuery::pq('tr') as $tr){
            $c++;
            if(count(\phpQuery::pq($tr)->find('td')) == 3 && $c > 1)
                $rastreamento[] = array('data'=>\phpQuery::pq($tr)->find('td:eq(0)')->text(),'local'=>\phpQuery::pq($tr)->find('td:eq(1)')->text(),'status'=>\phpQuery::pq($tr)->find('td:eq(2)')->text());
            if(count(\phpQuery::pq($tr)->find('td')) == 1 && $c > 1)
                $rastreamento[count($rastreamento)-1]['encaminhado'] = \phpQuery::pq($tr)->find('td:eq(0)')->text();
        }

        if(!count($rastreamento))
            return false;

        return $rastreamento;

        */

        /*
                $param = array(
                    'usuario' => 'ECT',
                    'senha' => 'SRO',
                    'tipo' => 'L',
                    'resultado' => 'T',
                    'lingua' => '101',
                    'objetos' => $codigo
                );

                $url = "http://webservice.correios.com.br/service/rastro/Rastro.wsdl";

                $soapClient = new \SoapClient($url, array(
                    'trace' => 1,
                    'exceptions'=> 0,
                    'encoding' => 'UTF-8',
                    'soapaction' => ''
                ));
                $dados = $soapClient->buscaEventos($param);

                dd($dados);
        */


    }

}