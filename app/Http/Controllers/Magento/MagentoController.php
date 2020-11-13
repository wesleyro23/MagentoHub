<?php

namespace App\Http\Controllers\Magento;

use App\Models\Parametro;
use Log;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class MagentoController extends Controller
{
    protected $conexaoMagento;
    
    public function __construct()
    {
        $parametros = Parametro::all();

        //Define parametros
        foreach ($parametros as $parametro){
            $conexaoMagento = $parametro->conexaoMagento;
        }

        $this->conexaoMagento = new \SoapClient($conexaoMagento);
    }

    public function getSessao($login,$senha)
    {
        try{
            $session = $this->conexaoMagento->login($login, $senha);
            Log::info('log', ['message' => 'Ação: login // OK -> Sessão criada '.$session]);
            return $session;
        }catch (\SoapFault $e){
            Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Sessão não foi criada']);
            return false;
        }
    }
    
    public function getCustomerGroupList($sessaoMagento)
    {
        try{
            $dados = $this->conexaoMagento->customerGroupList($sessaoMagento);
            Log::info('log', ['message' => 'Ação: customerGroupList // OK -> Lista de grupos']);
            return $dados;
        } catch (\SoapFault $e){
            Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Erro na Lista de Grupos']);
            return false;
        }
        
    }

    public function getItemMagentoBySKU($sessaoMagento, $sku, $storeViewMagento, $atributos, $tipoConsskuMagento)
    {
        try{
            $dados = $this->conexaoMagento->catalogProductInfo($sessaoMagento,$sku, $storeViewMagento, $atributos, $tipoConsskuMagento);
            Log::info('log', ['message' => 'Ação: catalogProductInfo // OK -> Item localizado '.$sku]);
            return $dados;
        } catch (\SoapFault $e){
            Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Item não localizado '.$sku]);
            return null;
        }
    }

    public function getListItemMagento($sessaoMagento)
    {
        try{
            $dados = $this->conexaoMagento->catalogProductList($sessaoMagento);
            Log::info('log', ['message' => 'Ação: catalogProductList // OK -> Itens Listados']);
            return $dados;
        } catch (\SoapFault $e){
            Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Itens não listados']);
            return false;
        }

    }
    
    public function createItemMagentoBySKU($sessaoMagento,$typeProdMagento,$atribListMagento,$sku,$itemMagento,$storeViewMagento)
    {
        try{
            $dados = $this->conexaoMagento->catalogProductCreate($sessaoMagento,$typeProdMagento,$atribListMagento,$sku,$itemMagento,$storeViewMagento);
            Log::info('log', ['message' => 'Ação: catalogProductCreate // OK -> Item incluido com sucesso '.$sku]);
            return true;
        } catch (\SoapFault $e){
            Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Erro ao incluir o Item '.$sku]);
            return false;
        }        
    }     
    
    public function updateItemMagentoBySKU($sessaoMagento, $sku, $itemMagento, $storeViewMagento, $tipoConsskuMagento)
    {
        try{
            $dados = $this->conexaoMagento->catalogProductUpdate($sessaoMagento, $sku, $itemMagento, $storeViewMagento, $tipoConsskuMagento);
            Log::info('log', ['message' => 'Ação: catalogProductUpdate // OK -> Atualizado com sucesso '.$sku]);
            return $dados;
        } catch (\SoapFault $e){
            Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Não autalizou o Item '.$sku]);
            return $e->getMessage();
        }
    }
    
    public function getCatalogCategoryTree($sessaoMagento,$parentId,$storeViewMagento)
    {
        try{
            $dados = $this->conexaoMagento->catalogCategoryTree($sessaoMagento,$parentId,$storeViewMagento);
            Log::info('log', ['message' => 'Ação: CatalogCategoryTree // OK -> Listou a Arvore']);
            return $dados;
        } catch (\SoapFault $e){
            Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Não listou']);
            return false;
        }
    }
    
    public function setCatalogProductCreate($sessaoMagento, $typeProdMagento, $atribListMagento, $sku, $productData)
    {
        try{
            $this->conexaoMagento->catalogProductCreate($sessaoMagento, $typeProdMagento, $atribListMagento, $sku, $productData);
            Log::info('log', ['message' => 'Ação: CatalogProductCreate // OK -> Gravou o Item '.$sku]);
            return true;            
        } catch (\SoapFault $e){
            Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Não Gravou o Item '.$sku]);
            return false;
        }
    }
    
    public function getSalesOrderList($sessaoMagento, $filtro){
        
        try{
            $dados = $this->conexaoMagento->salesOrderList($sessaoMagento, $filtro);
            Log::info('log', ['message' => 'Ação: salesOrderList // OK -> Pedidos Listados']);
            return $dados;
        } catch (\SoapFault $e){
            Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Pedidos não Listados']);
            return false;
        }
    }

    public function getSalesOrderInfo($sessaoMagento, $numeroPedido)
    {
        try{
            $dados = $this->conexaoMagento->salesOrderInfo($sessaoMagento, $numeroPedido);
            Log::info('log', ['message' => 'Ação: salesOrderInfo // OK -> Pedido Localizado: '.$numeroPedido]);
            return $dados;
        } catch (\SoapFault $e){
            Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Pedidos não Localizado '.$numeroPedido]);
            return false;
        }
    }
    
    public function getCustomerCustomerInfo($sessaoMagento, $clienteId)
    {
        try{
            $dados = $this->conexaoMagento->customerCustomerInfo($sessaoMagento, $clienteId);
            Log::info('log', ['message' => 'Ação: customerCustomerInfo // OK -> Cliente Localizado: '.$dados->firstname.' '.$dados->lastname]);
            return $dados;
        } catch (\SoapFault $e){
            Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Cliente não Localizado '.$clienteId]);
            return false;
        }
    }
    
    public function getCustomerAddressList($sessaoMagento, $clienteId)
    {
        try{
            $dados = $this->conexaoMagento->customerAddressList($sessaoMagento, $clienteId);
            Log::info('log', ['message' => 'Ação: customerAddressList // OK -> Endereços do Cliente Localizado: '.json_encode($dados)]);
            return $dados;            
        } catch (\SoapFault $e){
            Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Endereço do Cliente não Localizado '.$clienteId]);
            return false;
        }
    }

    public function getDirectoryRegionList($sessaoMagento, $countryId)
    {
        try{
            $dados = $this->conexaoMagento->directoryRegionList($sessaoMagento, $countryId);
            Log::info('log', ['message' => 'Ação: directoryRegionList // OK -> Regiões Localizadas do País '.$countryId]);
            return $dados;
        } catch (\SoapFault $e){
            Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Regiões não formam Localizadas do País '.$countryId]);
            return false;
        }
    }

    public function salesOrderShipmentCreate($sessaoMagento, $nrPedidoMagento, $itensExpedidos, $comment, $email, $includeComment)
    {
        try{
            $dados = $this->conexaoMagento->salesOrderShipmentCreate($sessaoMagento, $nrPedidoMagento, $itensExpedidos, $comment, $email, $includeComment);
            Log::info('log', ['message' => 'Ação: salesOrderShipmentCreate // OK -> Criando Entregas para o pedido '.$nrPedidoMagento]);
            return $dados;
        } catch (\SoapFault $e){
            Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Erro ao criar Entregas para o pedido '.$nrPedidoMagento]);
            return false;
        }

    }
    
    public function salesOrderShipmentList($sessaoMagento, $filtro)
    {
        try{
            $dados = $this->conexaoMagento->salesOrderShipmentList($sessaoMagento, $filtro);
            Log::info('log', ['message' => 'Ação: salesOrderShipmentList // OK -> Listando Entregas']);
            return $dados;            
        } catch (\SoapFault $e){
            Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Erro ao listar Entregas']);
            return false;
        }
    }
    
    public function salesOrderShipmentAddTrack($sessaoMagento, $cod_entrega, $codigoTransportador, $titulo, $cod_rastreamento)
    {
        try{
            $dados = $this->conexaoMagento->salesOrderShipmentAddTrack($sessaoMagento, $cod_entrega, $codigoTransportador, $titulo,$cod_rastreamento);
            Log::info('log', ['message' => 'Ação: salesOrderShipmentAddTrack // OK -> Enviando codigo de Rastreamento da Entrega '.$cod_entrega]);
            return $dados;
        } catch (\SoapFault $e){
            Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Erro ao enviar codigo de Rastreamento da Entrega '.$cod_entrega]);
            return false;
        }
    }

    public function salesOrderShipmentSendInfo($sessaoMagento, $cod_entrega, $chaveXml)
    {
        try{
            $dados = $this->conexaoMagento->salesOrderShipmentSendInfo($sessaoMagento, $cod_entrega, $chaveXml);
            Log::info('log', ['message' => 'Ação: salesOrderShipmentSendInfo // OK -> Enviado email referente a Entrega '.$cod_entrega]);
            return $dados;
        } catch (\SoapFault $e){
            Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Erro ao enviar email referente a Entrega '.$cod_entrega]);
            return false;
        }
    }
    
    public function catalogInventoryStockItemList($sessaoMagento, $listProducts)
    {
        try{
            $dados = $this->conexaoMagento->catalogInventoryStockItemList($sessaoMagento, $listProducts);
            //Log::info('log', ['message' => 'Ação: salesOrderShipmentSendInfo // OK -> Enviado email referente a Entrega '.$cod_entrega]);
            return $dados;
        } catch (\SoapFault $e){
            //Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Erro ao enviar email referente a Entrega '.$cod_entrega]);
            return false;
        }
    }

    public function catalogProductAttributeMediaList($sessaoMagento, $product, $storeView, $identifierType)
    {
        try{
            $dados = $this->conexaoMagento->catalogProductAttributeMediaList($sessaoMagento, $product, $storeView, $identifierType);
            //Log::info('log', ['message' => 'Ação: salesOrderShipmentSendInfo // OK -> Enviado email referente a Entrega '.$cod_entrega]);
            return $dados;
        } catch (\SoapFault $e){
            //Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Erro ao enviar email referente a Entrega '.$cod_entrega]);
            return false;
        }
    }

    public function salesOrderAddComment($sessaoMagento, $nrPedMagento, $status, $comment, $notify)
    {
        try{
            $dados = $this->conexaoMagento->salesOrderAddComment($sessaoMagento, $nrPedMagento, $status, $comment, $notify);
            Log::info('log', ['message' => 'Ação: salesOrderAddComment // OK -> Alterado o Status para Entregue do pedido '.$nrPedMagento]);
            return $dados;
        } catch (\SoapFault $e){
            Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Problemas ao alterar Status Entrega do pedido '.$nrPedMagento]);
            return false;
        }
    }
}
