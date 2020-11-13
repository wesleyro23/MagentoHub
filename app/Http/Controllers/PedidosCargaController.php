<?php

namespace App\Http\Controllers;

use App\Models\Transportadora;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Log;
use App\Http\Controllers\Datasul\DatasulController;
use App\Http\Controllers\Magento\MagentoController;
use App\Models\Magento\AssociativeEntity;
use App\Models\Parametro;
use App\Models\Pedido;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\DB;

class PedidosCargaController extends Controller
{
    protected $objMagento;
    protected $objDatasul;
    protected $parametros;
    protected $transportadoras;
    protected $request;

    public function __construct(MagentoController $objMagento, DatasulController $objDatasul, Parametro $parametros, Transportadora $transportadoras, Request $request)
    {
        $this->objMagento = $objMagento;
        $this->objDatasul = $objDatasul;
        $this->parametros = $parametros;
        $this->transportadoras = $transportadoras;
        $this->request = $request;
    }

    public function integracao()
    {
        $parametros = $this->parametros->all();

        //Define parametros
        foreach ($parametros as $parametro) {
            $loginMagento = $parametro->login_magento;
            $senhaMagento = $parametro->senha_magento;
            $statusPedidoParaIntegracao = $parametro->status_pedido_integracao;
            $emailRecebePed = $parametro->email;
        }

        //Recupera a sessao Magento
        $sessaoMagento = $this->objMagento->getSessao($loginMagento, $senhaMagento);

        //Busca Pedidos por Status
        $this->getPedidosPorStatus($sessaoMagento, $statusPedidoParaIntegracao);

        $pedidosIntegra = DB::table('pedidos')
            ->where('numero_pedido_datasul', '')->get();

        if ($pedidosIntegra != null) {
            $jsonped = json_encode($pedidosIntegra);

            Mail::send('emails.mailpedidos', ['pedidos' => $jsonped], function ($message) use ($emailRecebePed) {

                $message->to($emailRecebePed, 'Comercial');
                $message->from('portal@gruponks.com.br');
                $message->subject('Novos Pedidos no Magento');

            });
        }

        return view("pedidos.index", compact('pedidosIntegra'));

    }

    public function getPedidosPorStatus($sessaoMagento, $statusPedidoParaIntegracao)
    {

        //Filtro para Pedido
        $filtro = new \stdClass();

        $status = new AssociativeEntity();
        $status->setKey('status');
        $status->setValue($statusPedidoParaIntegracao);
        $filtro->filter[] = $status;
        $state = new AssociativeEntity();
        $state->setKey('state');
        $state->setValue($statusPedidoParaIntegracao);
        $filtro->filter[] = $state;

//        dd($filtro);

        //Lista Pedido por Status
        $listaPedidosMagento = $this->objMagento->getSalesOrderList($sessaoMagento, $filtro);

//        dd($listaPedidosMagento);

        foreach ($listaPedidosMagento as $pedidoMagento) {

            //Verifica se o pedido já existe no BD
            $pedidoIntegracao = DB::table('pedidos')
                ->where('numero_pedido_magento', $pedidoMagento->increment_id)->get();

            if ($pedidoIntegracao == null) {
                Pedido::create([
                    'numero_pedido_magento' => $pedidoMagento->increment_id,
                    'sincronizado_datasul' => false,
                    'sincronizado_magento' => false
                ]);
            }
        }

    }

    public function pedidosDatasulIntegracao()
    {
        $parametros = $this->parametros->all();

        //Define parametros
        foreach ($parametros as $parametro) {
            $loginMagento = $parametro->login_magento;
            $senhaMagento = $parametro->senha_magento;
            $loginErp = $parametro->login_erp;
            $senhaErp = $parametro->senha_erp;
            $lojaParam = $parametro->Loja;
        }

        //Recupera a sessao Magento
        $sessaoMagento = $this->objMagento->getSessao($loginMagento, $senhaMagento);

        //enviando Pedidos para o Datasul
        $dados = $this->enviaPedidoMagentoDatasul($sessaoMagento, $loginErp, $senhaErp, $lojaParam);

        $pedidosIntegra = DB::table('pedidos')
            ->where('numero_pedido_datasul', '')->get();

        return view("pedidos.index", compact('pedidosIntegra', 'dados'));

        //return redirect("/pedidosintegra");

    }

    public function enviaPedidoMagentoDatasul($sessaoMagento, $loginErp, $senhaErp, $lojaParam)
    {
        $listaPedidosPendentesDatasul = DB::table('pedidos')
            ->where([
                ['sincronizado_datasul', '=', false],
                ['sincronizado_magento', '=', false],
                ['numero_pedido_datasul', '=', '']
            ])->get();


        $objCliente = new ClientesCargaController();

        foreach ($listaPedidosPendentesDatasul as $pedido) {

            Log::debug('log', ['message' => "=======================INICIO TRANSAÇÃO PEDIDO: $pedido->numero_pedido_magento======================="]);

            //Recupera o pedido Magento por Numero do Pedido
            $pedidoMagento = $this->objMagento->getSalesOrderInfo($sessaoMagento, $pedido->numero_pedido_magento);

//            dd($pedidoMagento);

            /*if ($pedido->numero_pedido_magento == "100000100") {dd($pedidoMagento);}*/

            if ($pedidoMagento != false) {

                //Recupera a loja de origem do pedido
                if ($pedidoMagento->payment->method == "db1_anymarket") {
                    $comments = $pedidoMagento->status_history;
                    //dd($comments);
                    foreach ($comments as $item) {
                        //verifica e busca uma chave existente em um array
                        if (array_key_exists("comment", $item)) {
                            //quebra uma string por um marcador
                            $array = explode("<br>", $item->comment);

                            //Canal de Vendas imbutino tentro dos comentários do pedido
                            $canalVenda = $array[2];
                            //Só pega a loja se existir um conteudo expecifico
                            if (substr($canalVenda, 0, 24) == "<b>Canal de Vendas: </b>") {
                                //LOJA
                                $start1 = strpos($canalVenda, "</b>");
                                $loja = substr($canalVenda, $start1 + 4);
                            }

                            $idMark = $array[1];
                            if (substr($idMark,0,26) == "<b>Id no MarketPlace: </b>"){
                                //Id Marketplace
                                $start2 = strpos($idMark, "</b>");
                                $idMarketplace = substr($idMark, $start2 + 4);
                            }

                        }
                    }
                } elseif ($pedidoMagento->payment->method == "pagarme_cc") {
                    $loja = $lojaParam;
                    $idMarketplace = "";
                } elseif ($pedidoMagento->payment->method == "pagarme_boleto") {
                    $loja = $lojaParam;
                    $idMarketplace = "";
                } elseif ($pedidoMagento->payment->method == "checkmo") {
//                    $loja = $lojaParam;
                    $comments = $pedidoMagento->status_history;
//                    dd($comments);
                    foreach ($comments as $item) {
                        //verifica e busca uma chave existente em um array
                        if (array_key_exists("comment", $item)) {
                            //quebra uma string por um marcador
                            $array = explode("<br>", $item->comment);

                            //Canal de Vendas imbutino tentro dos comentários do pedido
                            $canalVenda = $array[2];
                            //Só pega a loja se existir um conteudo expecifico
                            if (substr($canalVenda, 0, 24) == "<b>Canal de Vendas: </b>") {
                                //LOJA
                                $start1 = strpos($canalVenda, "</b>");
                                $loja = substr($canalVenda, $start1 + 4);
                            }

                            $idMark = $array[1];
                            if (substr($idMark,0,26) == "<b>Id no MarketPlace: </b>"){
                                //Id Marketplace
                                $start2 = strpos($idMark, "</b>");
                                $idMarketplace = substr($idMark, $start2 + 4);
                            }

                        }
                    }
                }

//                dd($loja);
//                dd($idMarketplace);



                Log::info('log', ['message' => "Loja: " . $loja]);

                // Buscando dados do Cliente no Magento para ser cadastrado no datasul
                $clientePedidoMagento = $this->objMagento->getCustomerCustomerInfo($sessaoMagento, $pedidoMagento->customer_id);
                $enderecoClientePedidoMagento = $this->objMagento->getCustomerAddressList($sessaoMagento, $pedidoMagento->customer_id);

                /*if ($pedido->numero_pedido_magento == "100000100") {dd($enderecoClientePedidoMagento);}*/
//                dd($clientePedidoMagento);
//                dd($enderecoClientePedidoMagento);


                Log::info('log', ['message' => "Converte Cliente Magento em Cliente Datasul"]);
                $clienteDatasul = $objCliente->converteClienteMagentoClienteDatasul($clientePedidoMagento, $loja);

                /*if ($pedido->numero_pedido_magento == "100000111") {dd($clienteDatasul[0]->cpf_Cnpj);}*/
//                dd($clienteDatasul);

                Log::info('log', ['message' => "Converte Enderecos Cliente Magento em Enderecos Cliente Datasul"]);
                $enderecosDatasul = $objCliente->converteEnderecosClienteMagentoEnderecosClienteDatasul($sessaoMagento, $pedidoMagento, $enderecoClientePedidoMagento, $loja);

                /*if ($pedido->numero_pedido_magento == "100000100") {dd($enderecosDatasul);}*/
//                dd($enderecosDatasul);

                Log::info('log', ['message' => "Cadastrando Cliente no datasul."]);
                $requestCliente = $this->objDatasul->enviaClienteMagentoParaDatasul($clienteDatasul, $enderecosDatasul);

//                dd($requestCliente);

                if ($requestCliente['cRetorno'] == 'OK'){
//                if ($this->objDatasul->getMagConsultaCliente($clienteDatasul[0]->cpf_Cnpj)) {

                    Log::info('log', ['message' => 'OK -> Cliente Localizado!!!']);

                    Log::info('log', ['message' => "Convertendo Objeto Pedido e Itens do Pedido Magento para Objetos do Datasul..."]);
                    //Converter Pedido e Itens do Pedido de ObjetoPedidoMagentoPedidoDatasul( do Magento para Objetos do Datasul
                    $pedidoDatasul = $this->convertePedidoMagentoPedidoDatasul($pedidoMagento, $clientePedidoMagento, $loja, $idMarketplace);
                    $listaItensPedidoDatasul = $this->converteItensPedidoMagentoItensPedidoDatasul($pedidoMagento);


                    /*
                     * =======================================================================================================
                     * Analisar apartir daqui, implatação de clientes ok
                     * verificar perograma datasul para possiveis mudanças
                     * =======================================================================================================
                     */

//                    dd($pedidoDatasul);
//                    dd($listaItensPedidoDatasul);

                    Log::info('log', ['message' => "Pedido Datasul: " . json_encode($pedidoDatasul)]);
                    Log::info('log', ['message' => "Itens do Pedido Datasul: " . json_encode($listaItensPedidoDatasul)]);

                    Log::info('log', ['message' => "Enviando Pedido para o Datasul!!!"]);
                    $requestPedido = $this->objDatasul->enviaPedidoMagentoParaDatasul($senhaErp, $pedidoDatasul, $listaItensPedidoDatasul);

                    if ($requestPedido['chRetorno'] == 'OK') {

                        $updatePedido = Pedido::find($pedido->id);
                        $updatePedido->fill([
                            'numero_pedido_datasul' => $pedidoDatasul->numPedido,
                            'sincronizado_datasul' => true
                        ]);
                        $updatePedido->save();

                        Log::info('log', ['message' => "Pedido:" . $pedidoDatasul->numPedido . " Implantado com Sucesso!!!"]);
//                        $dados = new \stdClass();
//                        $dados->cRetorno = $requestPedido['chRetorno'];
//                        $dados->cCodmsg = $requestPedido['chCodMsg'];
//                        $dados->cMsg = $requestPedido['chMsg'];
//
//                        return $dados;
                    } else {
                        Log::info('log', ['message' => "Pedido não foi Criado no Datasul Porque?: " . $requestPedido['chMsg']]);
                        $dados = new \stdClass();
                        $dados->cRetorno = $requestPedido['chRetorno'];
                        $dados->cCodmsg = $requestPedido['chCodMsg'];
                        $dados->cMsg = $requestPedido['chMsg'];

                        return $dados;

                    }

                } else {

                    $dados = new \stdClass();

                    $dados->cRetorno = $requestCliente['cRetorno'];
                    $dados->cCodmsg = $requestCliente['cCodmsg'];
                    $dados->cMsg = $requestCliente['cMsg'];

                    return $dados;

                }

            }

            Log::debug('log', ['message' => "=======================FIM TRANSAÇÃO PEDIDO: $pedido->numero_pedido_magento======================="]);

        }

    }

    //Monta o pedido para envio ao Datadul
    public function convertePedidoMagentoPedidoDatasul($pedidoMagento, $clientePedidoMagento, $loja, $idMarketplace)
    {
        //dd($pedidoMagento);

        //$listaPedidoDatasul = array();

        $pedidoDatasul = new \stdClass();
        $pedidoDatasul->numPedido = "EC" . $pedidoMagento->increment_id;
        $dataPed = explode("-", substr($pedidoMagento->created_at, 0, 10));
        $pedidoDatasul->dataPedido = $dataPed[0] . $dataPed[1] . $dataPed[2];
        $cpf_cnpj = preg_replace("/[^0-9\s]/", "", $clientePedidoMagento->taxvat);
        $pedidoDatasul->cpfCgc = $cpf_cnpj;
        $pedidoDatasul->loja = $loja;

        /*
         * Fazer Tratamento especifico para tipos de pagamento através do metodo recebido
         *
         * Condição de Pagto que chega do portal
         * 1 = Cartao de Credito
         * 2 = Boleto
         * 3 = Débito em Conta
         */
        //Condição forçada para entrar no pedido
        //$pedidoDatasul->condPgto = 1;
        if ($pedidoMagento->payment->method == "pagarme_cc") {
            if ($pedidoMagento->payment->installments == 1) {
                $pedidoDatasul->condPgto = 2001;
            } elseif ($pedidoMagento->payment->installments == 2) {
                $pedidoDatasul->condPgto = 2002;
            } elseif ($pedidoMagento->payment->installments == 3) {
                $pedidoDatasul->condPgto = 2003;
            } elseif ($pedidoMagento->payment->installments == 4) {
                $pedidoDatasul->condPgto = 2004;
            } elseif ($pedidoMagento->payment->installments == 5) {
                $pedidoDatasul->condPgto = 2005;
            } elseif ($pedidoMagento->payment->installments == 6) {
                $pedidoDatasul->condPgto = 2006;
            } elseif ($pedidoMagento->payment->installments == 7) {
                $pedidoDatasul->condPgto = 2007;
            } elseif ($pedidoMagento->payment->installments == 8) {
                $pedidoDatasul->condPgto = 2008;
            } elseif ($pedidoMagento->payment->installments == 9) {
                $pedidoDatasul->condPgto = 2009;
            } elseif ($pedidoMagento->payment->installments == 10) {
                $pedidoDatasul->condPgto = 2010;
            } elseif ($pedidoMagento->payment->installments == 11) {
                $pedidoDatasul->condPgto = 2011;
            } elseif ($pedidoMagento->payment->installments == 12) {
                $pedidoDatasul->condPgto = 2012;
            }
            // TID da Transação do Pedido
            $pedidoDatasul->observacoes = "##TID:" . $pedidoMagento->payment->pagarme_transaction_id . "|" . $pedidoMagento->shipping_method . "##";
            $pedidoDatasul->loja = $loja;

        } elseif ($pedidoMagento->payment->method == "pagarme_boleto") {
            $pedidoDatasul->condPgto = 2000;
            $pedidoDatasul->observacoes = "##TID:" . $pedidoMagento->payment->pagarme_transaction_id . "|" . $pedidoMagento->shipping_method . "##";
            $pedidoDatasul->loja = $loja;
        } elseif ($pedidoMagento->payment->method == "db1_anymarket") {
            $pedidoDatasul->condPgto = 2000;
            $pedidoDatasul->observacoes = " ";
            //$pedidoDatasul->loja = $loja;
        } elseif ($pedidoMagento->payment->method == "checkmo") {
            $pedidoDatasul->condPgto = 2000; //2000;
            $comments = $pedidoMagento->status_history;
//                    dd($comments);
            foreach ($comments as $item) {
                //verifica e busca uma chave existente em um array
                if (array_key_exists("comment", $item)) {
                    //quebra uma string por um marcador
                    $array = explode("<br>", $item->comment);
                    //texto da quebra
//                    $comment = $array[2];
                    $pedidoDatasul->observacoes = "##TID:" . $array[0] . $array[1] . $array[2] . $array[3] . $array[4];
                }
            }
            $pedidoDatasul->loja = $loja;
        }

        $pedidoDatasul->frete = $pedidoMagento->shipping_amount;
        $pedidoDatasul->codEntrega = $pedidoMagento->increment_id{0} . $pedidoMagento->customer_id;

//        De-Para de Transportadora
//        dd($pedidoMagento->shipping_method);

        $transportadoras = $this->transportadoras->all();

        //Define transportadoras
        foreach ($transportadoras as $transp) {

            if ($transp->codigo_magento == $pedidoMagento->shipping_method) {
                $pedidoDatasul->transporte = $transp->codigo_datasul;
            }
        }

        $pedidoDatasul->idMarketplace = $idMarketplace;

//        dd($pedidoDatasul);

        return $pedidoDatasul; //$listaPedidoDatasul;
    }

    //Monta os itens do pedido para envio ao Datasul
    public function converteItensPedidoMagentoItensPedidoDatasul($pedidoMagento)
    {

        $listaItensPedidoDatasul = array();

        for ($i = 0; $i < count($pedidoMagento->items); $i++) {

            $item = $pedidoMagento->items[$i];

            $itemPedidoDatasul = new \stdClass();
            $itemPedidoDatasul->chCodItem = $item->sku;
            $itemPedidoDatasul->inQuantidade = $item->qty_ordered;
            $itemPedidoDatasul->dePreco = $item->price;
            $itemPedidoDatasul->deDesconto = $item->discount_amount / $item->qty_ordered;
//            $itemPedidoDatasul->deDesconto = $item->discount_percent;

            $listaItensPedidoDatasul[] = $itemPedidoDatasul;

        }

        return $listaItensPedidoDatasul;

    }

    public function getFormComunicaFaturamento()
    {
        return view('pedidos.formComunicaFaturamento');
    }

    public function postFormComunicaFaturamento()
    {
        $parametros = $this->parametros->all();

        //Define parametros
        foreach ($parametros as $parametro){
            $loginMagento = $parametro->login_magento;
            $senhaMagento = $parametro->senha_magento;
            $senhaErp = $parametro->senha_erp;
        }

        //Recupera a sessao Magento
        $sessaoMagento = $this->objMagento->getSessao($loginMagento, $senhaMagento);

        //recebe chave nfe por post
        $dados = $this->request->all();

        //Localiza pedido no Datasul para utilização de alguns campos
        $getMagConsultaInfoPedNota = $this->objDatasul->getMagConsultaInfoPedNota($senhaErp,"",$dados['chavenfe'], "", "");

        if ($getMagConsultaInfoPedNota['chRetorno'] == "OK"){

            $nrPedidoMagento = substr($getMagConsultaInfoPedNota['dsRetorno']->ttRetorno->chNrPedcli,2, strlen($getMagConsultaInfoPedNota['dsRetorno']->ttRetorno->chNrPedcli));

            $comFaturado = new \stdClass();
            $comFaturado->status = "complete_nf"; //Order status
            $comFaturado->comment = "Chave de Acesso: ".$dados['chavenfe']; //comentário enviado (opcional)
            $comFaturado->notify = 0;  //Notification flag (optional)

            //Comunica Faturamento
            $comentAddSuccess = $this->objMagento->salesOrderAddComment($sessaoMagento,$nrPedidoMagento,$comFaturado->status,$comFaturado->comment,$comFaturado->notify);

            return redirect("/formComunicaFaturamento");

        }
    }

    public function statusFaturado()
    {
        $parametros = $this->parametros->all();

        //Define parametros
        foreach ($parametros as $parametro){
            $loginMagento = $parametro->login_magento;
            $senhaMagento = $parametro->senha_magento;
            $senhaErp = $parametro->senha_erp;
        }

        //Recupera a sessao Magento
        $sessaoMagento = $this->objMagento->getSessao($loginMagento, $senhaMagento);

        //Recuperar pedidos com status de Pago
        //Filtro para Pedido
        $filtro = new \stdClass();
        $ae = new AssociativeEntity();
        $ae->setKey('status');
        $ae->setValue('processing');
        $filtro->filter[] = $ae;

        $pedidosMagento = $this->objMagento->getSalesOrderList($sessaoMagento, $filtro);

//        dd($pedidosMagento);

        foreach ($pedidosMagento as $pedido){

            //Retira caracteres da string
            $cpf_cnpj = preg_replace("/[^0-9\s]/", "", $pedido->customer_taxvat);

            //Localiza pedido no Datasul para utilização de alguns campos
            $getMagConsultaInfoPedNota = $this->objDatasul->getMagConsultaInfoPedNota($senhaErp,"","", "EC".$pedido->increment_id, $cpf_cnpj);

//                dd($getMagConsultaInfoPedNota);

            if ($getMagConsultaInfoPedNota['chRetorno'] == "OK"){

//                dd(isset($getMagConsultaInfoPedNota['dsRetorno']->ttRetorno->chChave));

                if (isset($getMagConsultaInfoPedNota['dsRetorno']->ttRetorno->chChave)){

//                    dd($getMagConsultaInfoPedNota['dsRetorno']->ttRetorno->chChave);

                    $comFaturado = new \stdClass();
                    $comFaturado->status = "complete_nf"; //Order status
                    $comFaturado->comment = "Chave de Acesso: ".$getMagConsultaInfoPedNota['dsRetorno']->ttRetorno->chChave; //comentário enviado (opcional)
                    $comFaturado->notify = 1;  //Notification flag (optional)

                    //Comunica Faturamento
                    $this->objMagento->salesOrderAddComment($sessaoMagento,$pedido->increment_id,$comFaturado->status,$comFaturado->comment,$comFaturado->notify);

                }

            }

        }

    }
}
