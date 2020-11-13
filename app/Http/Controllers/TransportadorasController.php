<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Datasul\DatasulController;
use App\Http\Controllers\Magento\MagentoController;
use App\Models\ItemExped;
use App\Models\Magento\AssociativeEntity;
use App\Models\Parametro;
use App\Models\Rastreamento;
use App\Models\Transportadora;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Log;

class TransportadorasController extends Controller
{
    protected $conexaoTransportadoras;
    protected $request;
    protected $objMagento;
    protected $objDatasul;
    protected $parametros;
    protected $transportadora;


    public function __construct(Request $request, MagentoController $objMagento, DatasulController $objDatasul, Parametro $parametros, Transportadora $transportadora)
    {
        $this->request = $request;
        $this->objMagento = $objMagento;
        $this->objDatasul = $objDatasul;
        $this->parametros = $parametros;
        $this->transportadora = $transportadora;
        $this->conexaoTransportadoras = new Client();
        //{"document":"08716691709"}
        //$this->conexaoDatasul = new \SoapClient('http://192.168.0.23:8082/wsatst/ws2skntst/wsdl?targetURI=urn:Magento');
    }


    public function getBuscaRastreioTransportador()
    {
        $parametros = $this->parametros->all();

        //Define parametros
        foreach ($parametros as $parametro){
            $loginMagento = $parametro->login_magento;
            $senhaMagento = $parametro->senha_magento;
            $senhaErp = $parametro->senha_erp;
            $lojaParam = $parametro->Loja;
        }

        //Recupera a sessao Magento
        $sessaoMagento = $this->objMagento->getSessao($loginMagento,$senhaMagento);

        $comment = new \stdClass();
        $comment->descr = "Enviando encomenda ao Transportador"; //comentário enviado (opcional)
        $comment->email = 0; //Envio de Email (opcional)
        $comment->includeComment = 1;  //Incluir Comentário no Email (opcional)


        //Busca pedidos em aberto no sistema
        $listaPedidosPendentes = DB::table('pedidos')
            ->where([
                ['sincronizado_datasul', '=',true],
                ['sincronizado_magento', '=',false],
                ['numero_pedido_datasul', '<>', '']
            ])->get();

//        dd($listaPedidosPendentes);

        //Recuperarndo documento dos clientes no Pedido Magento
        for ($i = 0; $i < count($listaPedidosPendentes); $i++){

            $pedMagento = $this->objMagento->getSalesOrderInfo($sessaoMagento,$listaPedidosPendentes[$i]->numero_pedido_magento);

            if ($pedMagento->status == "closed" or $pedMagento->status == "canceled"){

                DB::table('pedidos')
                    ->where('numero_pedido_magento', '=', $pedMagento->increment_id)
                    ->update([
                        'sincronizado_magento' => true,
                        'pedido_valido_magento' => true
                    ]);
            }

//            dd("diferente de closed ou canceled: ".$pedMagento->increment_id);

            if ($pedMagento->status <> "closed" and $pedMagento->status <> "canceled"){

//                dd($pedMagento);

                //transportador FM Transportes
                if ($pedMagento->shipping_method == 'akhilleus_FMT_1') {

                    //Recupera documeto do cliente CPF/CNPJ
                    $docCliente = preg_replace("/[^0-9\s]/", "", $pedMagento->customer_taxvat);

                    //Localiza pedido no Datasul para utilização de alguns campos
                    $getMagConsultaInfoPedNota = $this->objDatasul->getMagConsultaInfoPedNota($senhaErp,$lojaParam,"", $listaPedidosPendentes[$i]->numero_pedido_datasul, $docCliente);

//                dd($getMagConsultaInfoPedNota);

                    //recupera rastreio no
                    $url = 'http://api.producao.alfatracking.com.br/api/v1/client/order/searchtracker';
                    $data = ['document' => $docCliente];

                    $response = $this->conexaoTransportadoras->post($url, [
                        'body' => json_encode($data),
                        'headers' => [
                            'Content-Type' => 'application/json'
                        ]
                    ]);

                    //Converte em um array a consulta retornada da FM transporte atraves do WS
                    $consultaTransp = json_decode(preg_replace('~[\r\n]+~', '', $response->getBody(true)->getContents()));

//                dd($consultaTransp);

                    if ($consultaTransp <> null){

                        /***************Maior valor do array de retorno**************/
                        function maxValueInArray($array, $keyToSearch)
                        {
                            $currentMax = NULL;
                            foreach($array as $arr)
                            {
                                foreach($arr as $key => $value)
                                {
                                    if ($key == $keyToSearch && ($value >= $currentMax))
                                    {
                                        $currentMax = $value;
                                    }
                                }
                            }

                            return $currentMax;
                        }

                        //Utiliza a função para recuperar a ultima data do array retornado pelo WS
                        $value = maxValueInArray($consultaTransp, "date");
                        /************************************/

                        for ($i = 0; $i < count($consultaTransp); $i++){

                            //Se a data retornada pela função for igual a alguma data retornada pela iteração ele entra
                            if ($value == $consultaTransp[$i]->date){

                                // dd($value);

                                //Crinando entrega no Magento
                                return $this->criaEntregaMagentoTransp($sessaoMagento, $pedMagento, $consultaTransp[0]->barCode, $comment, $getMagConsultaInfoPedNota);
                            }
//                    $date = date('d-m-Y', strtotime($consultaTransp[$i]->date)) ;
                        }

                    }

                } elseif ($pedMagento->shipping_method == 'akhilleus_5' or $pedMagento->shipping_method == 'akhilleus_3'){
                    //Recupera documeto do cliente CPF/CNPJ
                    $docCliente = preg_replace("/[^0-9\s]/", "", $pedMagento->customer_taxvat);

                    //Localiza pedido no Datasul para utilização de alguns campos
                    $getMagConsultaInfoPedNota = $this->objDatasul->getMagConsultaInfoPedNota($senhaErp,$lojaParam,"", $listaPedidosPendentes[$i]->numero_pedido_datasul, $docCliente);

//                dd($getMagConsultaInfoPedNota['dsRetorno']->ttRetorno->chChave);

                    //recupera rastreio no
                    $url = 'http://www.jadlog.com.br/embarcador/api/tracking/consultar';
                    $data = array('consulta' => [[
                        'df'=> [
                            'danfe' => $getMagConsultaInfoPedNota['dsRetorno']->ttRetorno->chChave,
                            'cnpjRemetente' => '29227945000203'
                        ]
                    ]]);

                    $response = $this->conexaoTransportadoras->post($url, [
                        'body' => json_encode($data),
                        'headers' => [
                            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOjcxODg4fQ.xtkyLOFQH8Q9j7dLHSBe0vK-TiRaBJnRP4KwL9EAUyM',
                            'Content-Type' => 'application/json'
                        ]
                    ]);

                    $consultaTransp = json_decode($response->getBody(true)->getContents());

                    if ($consultaTransp <> null){

                        if (isset($consultaTransp->consulta[0]->error) == false){

                            return $this->criaEntregaMagentoTransp($sessaoMagento, $pedMagento, $consultaTransp->consulta[0]->tracking->codigo, $comment, $getMagConsultaInfoPedNota);

                        }

                    }

                }

            }

        }
    }

    public function criaEntregaMagentoTransp($sessaoMagento,$pedMagento,$tracking,$comment, $getMagConsultaInfoPedNota)
    {

        Log::info('log', ['message' => "Criando entrega no Magento..."]);

        //Verifica se já existe uma entrega para oa pedido
        $getEntregaMagento = $this->getEntregaMagento($sessaoMagento,$pedMagento);

//        dd($getEntregaMagento);

        if($getEntregaMagento == ""){

            //Lista de itens a serem enviados na criação da entrega
            $itensExpedidos = array();

            $contador = 0;

            // Localizando IDs do item no pedido Magento e montando OrderItemIdQty[] para o Magento
            foreach ($pedMagento->items as $itemMagento) {

                $orderItem = new \stdClass();
                $orderItem->order_item_id = $itemMagento->item_id;
                $orderItem->qty = $itemMagento->qty_ordered;
                $itensExpedidos[$contador] = $orderItem;

                $contador++;
            }

//            dd($itensExpedidos);
//            dd($pedMagento);
//            dd($getMagConsultaInfoPedNota['dsRetorno']->ttRetorno);

            //Criar Entrega no Magento
            $codigoEntregaMagento = $this->objMagento->salesOrderShipmentCreate($sessaoMagento, $pedMagento->increment_id, $itensExpedidos, $comment->descr, $comment->email, $comment->includeComment);

            if ($codigoEntregaMagento != null){
                //Persistindo no Banco Integrador

                foreach ($pedMagento->items as $item) {

                    $itemExped = ItemExped::create([
                        'sku' => $item->sku,
                        'qtd' => $item->qty_ordered
                    ]);

                    $rastreamento = Rastreamento::create([
                        'cod_rastreamento' => $tracking
                    ]);

                    if ($rastreamento != null){
                        $itemExped->rastreamentos()->save($rastreamento);

                        $rastreamento->itemexpeds()->updateExistingPivot($itemExped->id, [
                            "cod_entrega" => $codigoEntregaMagento,
                            'key_xml' => $getMagConsultaInfoPedNota['dsRetorno']->ttRetorno->chChave,
                            'sincronizado' => false,
                            'nr_ped_datasul' => $getMagConsultaInfoPedNota['dsRetorno']->ttRetorno->chNrPedcli,
                            'nr_ped_magento' => $pedMagento->increment_id
                        ]);
                    }

                }

                return "OK";

            } else{
                return "Erro ao gerar Entregas no Magento, verifique o Log de eventos.";
            }
        } else{

            $consultaCodEntregaIntegrador = DB::table('rastreamentos_itemexpeds')
                ->where('cod_entrega', $getEntregaMagento)->get();

//            dd($consultaCodEntregaIntegrador);

            if ($consultaCodEntregaIntegrador == null){

                foreach ($pedMagento->items as $item) {

                    $itemExped = ItemExped::create([
                        'sku' => $item->sku,
                        'qtd' => $item->qty_ordered
                    ]);

                    $rastreamento = Rastreamento::create([
                        'cod_rastreamento' => $tracking
                    ]);

                    if ($rastreamento != null){
                        $itemExped->rastreamentos()->save($rastreamento);

                        $rastreamento->itemexpeds()->updateExistingPivot($itemExped->id, [
                            "cod_entrega" => $getEntregaMagento,
                            'key_xml' => $getMagConsultaInfoPedNota['dsRetorno']->ttRetorno->chChave,
                            'sincronizado' => false,
                            'nr_ped_datasul' => $getMagConsultaInfoPedNota['dsRetorno']->ttRetorno->chNrPedcli,
                            'nr_ped_magento' => $pedMagento->increment_id
                        ]);
                    }

                }

            } else{
                return redirect("/");
            }

        }


    }

    public function getEntregaTransportador()
    {
        $parametros = $this->parametros->all();

        //Define parametros
        foreach ($parametros as $parametro){
            $loginMagento = $parametro->login_magento;
            $senhaMagento = $parametro->senha_magento;
            $senhaErp = $parametro->senha_erp;
            $lojaParam = $parametro->Loja;

        }

        //Recupera a sessao Magento
        $sessaoMagento = $this->objMagento->getSessao($loginMagento,$senhaMagento);

        //buscando lista de itens sem sincronizar
        $listaItensPendentesTrack = DB::table('rastreamentos_itemexpeds')
            ->select('rastreamentos_itemexpeds.cod_entrega','rastreamentos_itemexpeds.nr_ped_datasul','rastreamentos_itemexpeds.nr_ped_magento')
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

            $pedMagento = $this->objMagento->getSalesOrderInfo($sessaoMagento,$codentrega->nr_ped_magento);

//            dd($pedMagento);

            $listaRastreios = DB::table('rastreamentos_itemexpeds')
                ->join('rastreamentos','rastreamentos_itemexpeds.id_rastreamento', '=', 'rastreamentos.id')
                ->select('rastreamentos_itemexpeds.nr_ped_magento', 'rastreamentos_itemexpeds.cod_entrega', 'rastreamentos.cod_rastreamento')
                ->where([
                    ['cod_entrega', '=', $codentrega->cod_entrega]
                ])
                ->get();

//            dd($listaRastreios);

            //for buscando numero dos rastreios
            for ($i = 0; $i < count($listaRastreios); $i++){

                //Passo o restreio para saber a situação da entrega
                $retornoEntrega = $this->rastrearTransp($listaRastreios[$i], $pedMagento, $senhaErp, $lojaParam);

//                dd($retornoEntrega);

                //se retornoEntrega for igual a 4 entrgou então entra
                if (($retornoEntrega == 5) or ($retornoEntrega == "ENTREGUE")){

                    //Notificação por email
                    $notification = new \stdClass();
                    $notification->comment = "Entrega Efetuada..."; //comentário enviado (opcional)
                    $notification->status = "entregue"; //Status do Pediso
                    $notification->notify = true; //Envio de Email (opcional)

                    //Altera status do pedido para entregue
                    $entregaEfetuada = $this->objMagento->salesOrderAddComment($sessaoMagento,$pedMagento->increment_id,$notification->status,$notification->comment,$notification->notify);

                    //Altera status no integrador para entregue
                    if ($entregaEfetuada){

                        DB::table('rastreamentos_itemexpeds')
                            ->where('cod_entrega', '=', $listaRastreios[$i]->cod_entrega)
                            ->update(['entrega_correios' => true]);
                    }

                }

            }
        }

    }

    public function rastrearTransp($listaRastreios, $pedMagento, $senhaErp, $lojaParam)
    {

        if ($pedMagento->shipping_method == "akhilleus_FMT_1"){

            //recupera rastreio no
            $url = 'http://api.producao.alfatracking.com.br/api/v1/client/order/searchtracker';
            $data = ['barCode' => $listaRastreios->cod_rastreamento];

            $response = $this->conexaoTransportadoras->post($url, [
                'body' => json_encode($data),
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

            //Converte em um array
            $consultaTransp = json_decode(preg_replace('~[\r\n]+~', '', $response->getBody(true)->getContents()));

            if ($consultaTransp <> null){

                /***************Maior valor do array de retorno**************/
                function maxValueInArray($array, $keyToSearch)
                {
                    $currentMax = NULL;
                    foreach($array as $arr)
                    {
                        foreach($arr as $key => $value)
                        {
                            if ($key == $keyToSearch && ($value >= $currentMax))
                            {
                                $currentMax = $value;
                            }
                        }
                    }

                    return $currentMax;
                }

                $value = maxValueInArray($consultaTransp, "date");
                /************************************/

                for ($i = 0; $i < count($consultaTransp); $i++){

                    if ($value == $consultaTransp[$i]->date){

                        //retorna status de emtregue
                        return $consultaTransp[$i]->status;

                    }
                }
            }

        }
        elseif ($pedMagento->shipping_method == 'akhilleus_5'){

            $docCliente = preg_replace("/[^0-9\s]/", "", $pedMagento->customer_taxvat);

            //Localiza pedido no Datasul para utilização de alguns campos
            $getMagConsultaInfoPedNota = $this->objDatasul->getMagConsultaInfoPedNota($senhaErp,$lojaParam,"", "EC".$pedMagento->increment_id, $docCliente);

            //recupera rastreio no
            $url = 'http://www.jadlog.com.br/embarcador/api/tracking/consultar';
            $data = array('consulta' => [[
                'df'=> [
                    'danfe' => $getMagConsultaInfoPedNota['dsRetorno']->ttRetorno->chChave,
                    'cnpjRemetente' => '29227945000203'
                ]
            ]]);

            $response = $this->conexaoTransportadoras->post($url, [
                'body' => json_encode($data),
                'headers' => [
                    'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOjcxODg4fQ.xtkyLOFQH8Q9j7dLHSBe0vK-TiRaBJnRP4KwL9EAUyM',
                    'Content-Type' => 'application/json'
                ]
            ]);

            $consultaTransp = json_decode($response->getBody(true)->getContents());

            return $consultaTransp->consulta[0]->tracking->status;

        }

    }

    public function getEnviaRastreioTransp()
    {
        $parametros = $this->parametros->all();

        //Define parametros
        foreach ($parametros as $parametro){
            $loginMagento = $parametro->login_magento;
            $senhaMagento = $parametro->senha_magento;
        }

        //Recupera a sessao Magento
        $sessaoMagento = $this->objMagento->getSessao($loginMagento,$senhaMagento);

        //Verificar funcionamento no envio
//        $requestTrack = $this->enviaTrackMagentoTransp($sessaoMagento);

//        if ($requestTrack == "OK"){
//            return redirect("/");
//        }

    }

    public function enviaTrackMagentoTransp($sessaoMagento)
    {
        //buscando lista de itens sem sincronizar
        $listaItensPendentesTrack = DB::table('rastreamentos_itemexpeds')
            ->where([
                ['sincronizado', '=', false],
                ['cod_entrega', '<>', '']
            ])->get();


//        dd($listaItensPendentesTrack);

        $contador = 1;

        foreach ($listaItensPendentesTrack as $item) {

            $pedMagento = $this->objMagento->getSalesOrderInfo($sessaoMagento,$item->nr_ped_magento);

            $nomeTransp = DB::table('transportadoras')
                ->select('codigo_datasul')
                ->where([
                    ['codigo_magento', '=', $pedMagento->shipping_method]
                ])->get();

//            dd($nomeTransp[0]->codigo_datasul);

            $rastreamento = Rastreamento::find($item->id_rastreamento);

//            dd($rastreamento->cod_rastreamento);

            $addTrackEntregaMagento = $this->objMagento->salesOrderShipmentAddTrack($sessaoMagento, $item->cod_entrega, "custom", $nomeTransp[0]->codigo_datasul." - Vol: ".$contador, $rastreamento->cod_rastreamento);

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

    public function getFormEnviaProdFunc()
    {
        return view('transportadoras.formEnviaProdFunc');
    }

    public function postFormEnviaProdFunc()
    {
        //recebe xml por post
        $dados = $this->request->all();

        $parametros = $this->parametros->all();

        //Define parametros
        foreach ($parametros as $parametro){
            $loginMagento = $parametro->login_magento;
            $senhaMagento = $parametro->senha_magento;
            $senhaErp = $parametro->senha_erp;
            $lojaParam = $parametro->Loja;
        }

        //Recupera a sessao Magento
        $sessaoMagento = $this->objMagento->getSessao($loginMagento,$senhaMagento);

        $getMagConsultaInfoPedNota = $this->objDatasul->getMagConsultaInfoPedNota($senhaErp,$lojaParam,$dados['chavenfe'],"","");

        $nrPedMagento = substr($getMagConsultaInfoPedNota['dsRetorno']->ttRetorno->chNrPedcli,2,9);

        $pedMagento = $this->objMagento->getSalesOrderInfo($sessaoMagento,$nrPedMagento);

//        dd($pedMagento);

        $comment = new \stdClass();
        $comment->descr = "Enviando encomenda";
        $comment->email = 0;
        $comment->includeComment = 1;

        return $this->criarEntregaMagentoFunc($sessaoMagento, $pedMagento, $comment, $getMagConsultaInfoPedNota);

    }

    public function criarEntregaMagentoFunc($sessaoMagento, $pedMagento, $comment, $getMagConsultaInfoPedNota)
    {

        $itensExpedidos = array();

        $contador = 0;

        foreach ($pedMagento->items as $itemMagento) {

            $orderItem = new \stdClass();
            $orderItem->order_item_id = $itemMagento->item_id;
            $orderItem->qty = $itemMagento->qty_ordered;
            $itensExpedidos[$contador] = $orderItem;

            $contador++;
        }

        //dd($itensExpedidos);

        Log::info('log', ['message' => "Criando entrega no Magento..."]);

        //Verifica se já existe uma entrega para oa pedido
        $getEntregaMagento = $this->getEntregaMagento($sessaoMagento,$pedMagento);

//        dd($getEntregaMagento);

        if($getEntregaMagento == ""){

            //Criar Entrega no Magento
            $codigoEntregaMagento = $this->objMagento->salesOrderShipmentCreate($sessaoMagento, $pedMagento->increment_id, $itensExpedidos, $comment->descr, $comment->email, $comment->includeComment);

            if ($codigoEntregaMagento != null){
                //Persistindo no Banco Integrador

                foreach ($pedMagento->items as $item) {

                    $itemExped = ItemExped::create([
                        'sku' => $item->sku,
                        'qtd' => $item->qty_ordered
                    ]);

                    $rastreamento = Rastreamento::create([
                        'cod_rastreamento' => $codigoEntregaMagento
                    ]);

                    if ($rastreamento != null){
                        $itemExped->rastreamentos()->save($rastreamento);

                        $rastreamento->itemexpeds()->updateExistingPivot($itemExped->id, [
                            "cod_entrega" => $codigoEntregaMagento,
                            'key_xml' => $getMagConsultaInfoPedNota['dsRetorno']->ttRetorno->chChave,
                            'sincronizado' => false,
                            'nr_ped_datasul' => $getMagConsultaInfoPedNota['dsRetorno']->ttRetorno->chNrPedcli,
                            'nr_ped_magento' => $pedMagento->increment_id
                        ]);
                    }

                }

                $requestTrack = $this->enviaTrackMagentoFunc($sessaoMagento, $getMagConsultaInfoPedNota);

//                dd($requestTrack);

                if ($requestTrack == "OK"){
                    $getEntregaFunc = $this->getEntregaFunc($sessaoMagento,$pedMagento->increment_id);
                    if ($getEntregaFunc == "OK"){
                        return redirect("/formEnviaProdFunc");
                    }
                }


            } else{
                return "Erro ao gerar Entregas no Magento, verifique o Log de eventos.";
            }

        } else{

            $consultaCodEntregaIntegrador = DB::table('rastreamentos_itemexpeds')
                ->where('cod_entrega', $getEntregaMagento)->get();

//            dd($consultaCodEntregaIntegrador);

            if ($consultaCodEntregaIntegrador == null){

                foreach ($pedMagento->items as $item) {

                    $itemExped = ItemExped::create([
                        'sku' => $item->sku,
                        'qtd' => $item->qty_ordered
                    ]);

                    $rastreamento = Rastreamento::create([
                        'cod_rastreamento' => $getEntregaMagento
                    ]);

                    if ($rastreamento != null){
                        $itemExped->rastreamentos()->save($rastreamento);

                        $rastreamento->itemexpeds()->updateExistingPivot($itemExped->id, [
                            "cod_entrega" => $getEntregaMagento,
                            'key_xml' => $getMagConsultaInfoPedNota['dsRetorno']->ttRetorno->chChave,
                            'sincronizado' => false,
                            'nr_ped_datasul' => $getMagConsultaInfoPedNota['dsRetorno']->ttRetorno->chNrPedcli,
                            'nr_ped_magento' => $pedMagento->increment_id
                        ]);
                    }
                }

                $requestTrack = $this->enviaTrackMagentoFunc($sessaoMagento, $getMagConsultaInfoPedNota);

                if ($requestTrack == "OK"){
                    $getEntregaFunc = $this->getEntregaFunc($sessaoMagento,$pedMagento->increment_id);
                    if ($getEntregaFunc == "OK"){
                        return redirect("/formEnviaProdFunc");
                    }
                }

            } else{

                $requestTrack = $this->enviaTrackMagentoFunc($sessaoMagento, $getMagConsultaInfoPedNota);

//                dd($pedMagento->increment_id);

                if ($requestTrack == "OK"){
                    $getEntregaFunc = $this->getEntregaFunc($sessaoMagento,$pedMagento->increment_id);
                    if ($getEntregaFunc == "OK"){
                        return redirect("/formEnviaProdFunc");
                    }
                }
            }

        }


    }

    public function enviaTrackMagentoFunc($sessaoMagento, $getMagConsultaInfoPedNota)
    {

        //buscando lista de itens sem sincronizar
        $listaItensPendentesTrack = DB::table('rastreamentos_itemexpeds')
            ->where([
                ['sincronizado', '=', false],
                ['cod_entrega', '<>', ''],
                ['nr_ped_datasul', '=', $getMagConsultaInfoPedNota['dsRetorno']->ttRetorno->chNrPedcli]
            ])->get();

        $contador = 1;

        foreach ($listaItensPendentesTrack as $item) {

            $rastreamento = Rastreamento::find($item->id_rastreamento);

//            dd($rastreamento);

            $addTrackEntregaMagento = $this->objMagento->salesOrderShipmentAddTrack($sessaoMagento, $item->cod_entrega, "akhilleus", "Vol: ".$contador, $rastreamento->cod_rastreamento);

            //dd($addTrackEntregaMagento);

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


    /*Merda Grande*/
    public function getEntregaFunc($sessaoMagento, $nrPedidoMagento)
    {
        //buscando lista de itens sem sincronizar
        $listaItensPendentesTrack = DB::table('rastreamentos_itemexpeds')
            ->select('rastreamentos_itemexpeds.cod_entrega')
            ->where([
                ['nr_ped_magento', '=', $nrPedidoMagento],
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

            $entregaFunc = new \stdClass();
            $entregaFunc->comment = "Entrega Efetuada...";
            $entregaFunc->status = 'entregue';
            $entregaFunc->notify = true;

            //Altera status do pedido para entregue
            $entregaEfetuada = $this->objMagento->salesOrderAddComment($sessaoMagento,$listaRastreios[0]->nr_ped_magento,$entregaFunc->status,$entregaFunc->comment,$entregaFunc->notify);

            //Altera status no integrador para entregue
            if ($entregaEfetuada){

                DB::table('rastreamentos_itemexpeds')
                    ->where('cod_entrega', '=', $codentrega->cod_entrega)
                    ->update(['entrega_correios' => 1]);
            }

        }

        return "OK";

    }

    public function getEntregaMagento($sessaoMagento,$pedMagento)
    {
        //Filtro para verificar entrega
        $filtro = new \stdClass();
        $ae = new AssociativeEntity();
        $ae->setKey('order_id');
        $ae->setValue($pedMagento->order_id);
        $filtro->filter[] = $ae;

        $listaEntregaMagento = $this->objMagento->salesOrderShipmentList($sessaoMagento, $filtro);

        if ($listaEntregaMagento == null){
            $codEntregaMagento = "";
        } else{
            $codEntregaMagento = end($listaEntregaMagento)->increment_id;
        }

        return $codEntregaMagento;

    }

}
