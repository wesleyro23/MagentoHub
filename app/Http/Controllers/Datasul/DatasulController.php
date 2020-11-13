<?php

namespace App\Http\Controllers\Datasul;

use App\Models\Parametro;
use Log;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class DatasulController extends Controller
{

    protected $conexaoDatasul;

    public function __construct()
    {
        $parametros = Parametro::all();

        //Define parametros
        foreach ($parametros as $parametro){
            $conexaoDatasul = $parametro->conexaoDatasul;
        }

        $this->conexaoDatasul = new \SoapClient($conexaoDatasul);
    }


    public function getListaItensDatasulByTabelaPreco($tabelaPrecoErp,$loja)
    {
        try{
            $dados = $this->conexaoDatasul->getMagBuscaProdutos($tabelaPrecoErp,$loja);
            Log::info('log', ['message' => 'Ação: getMagBuscaProdutos // OK -> Lista de Produtos Localizados da Tabela '.$tabelaPrecoErp]);
            return $dados;
        } catch (\SoapFault $e){
            Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Erro ao tentar localizar lista de Produtos da Tabela '.$tabelaPrecoErp]);
            return false;
        }
    }
    
    public function enviaClienteMagentoParaDatasul($clienteDatasul, $enderecosDatasul)
    {
        try{
            $dados = $this->conexaoDatasul->setMagCriarCliente($clienteDatasul, $enderecosDatasul);
            Log::info('log', ['message' => 'Ação: setMagCriarCliente // '.$dados['cRetorno'].' -> '.$dados['cMsg']]);
            return $dados;
        } catch (\SoapFault $e){
            Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Erro ao cadastrar cliente no datasul.']);
            return false;
        }
    }
    
    public function enviaPedidoMagentoParaDatasul($senhaErp, $pedidoDatasul, $listaItensPedidoDatasul)
    {
//        dd($senhaErp);
//        dd($pedidoDatasul);
        //dd($listaItensPedidoDatasul);

        try{
            //$dados = $this->conexaoDatasul->setMagCriarPedido($loginErp, $senhaErp, $pedidoDatasul, $listaItensPedidoDatasul);
            $dados = $this->conexaoDatasul->setMagIntegraPedidoWeb($senhaErp, $pedidoDatasul->numPedido, $pedidoDatasul->dataPedido, $pedidoDatasul->cpfCgc, $pedidoDatasul->loja, $pedidoDatasul->condPgto, $pedidoDatasul->frete, $pedidoDatasul->observacoes, $pedidoDatasul->codEntrega, $pedidoDatasul->transporte, $pedidoDatasul->idMarketplace, $listaItensPedidoDatasul);
            Log::info('log', ['message' => 'Ação: setMagCriarPedido // '.$dados['chRetorno'].' -> '.$dados['chMsg']]);
            return $dados;
        } catch (\SoapFault $e){
            Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Erro ao implantar pedido no datasul.']);
            return false;
        }
    }

    public function getMagSaldoEstoque($loja, $itemDatasul)
    {
        try{
            $dados = $this->conexaoDatasul->getMagSaldoEstoque($loja, $itemDatasul);
            //Log::info('log', ['message' => 'Ação: setMagCriarPedido // '.$dados['cRetorno'].' -> '.$dados['cMsg']]);
            return $dados;
        } catch (\SoapFault $e){
            //Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Erro ao implantar pedido no datasul.']);
            return false;
        }
    }

    public function getCidadeIBGE($codibge)
    {
        try{
            $dados = $this->conexaoDatasul->getCidadeIBGE($codibge);
            //Log::info('log', ['message' => 'Ação: setMagCriarPedido // '.$dados['cRetorno'].' -> '.$dados['cMsg']]);
            return $dados;
        }catch (\SoapFault $e){
            //Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Erro ao implantar pedido no datasul.']);
            return false;
        }
    }

    public function getMagConsultaCliente($cpfCnpj)
    {
        try{
            $dados = $this->conexaoDatasul->getMagConsultaCliente($cpfCnpj);
            //Log::info('log', ['message' => 'Ação: setMagCriarPedido // OK -> Cliente Localizado!!!']);
            return $dados;
        }catch (\SoapFault $e){
            Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Cliente nao Localizado.']);
            return false;
        }
    }

    public function getMagConsultaInfoPedNota($senha, $loja, $chave, $ped, $cgc)
    {
        try{
            $dados = $this->conexaoDatasul->getMagConsultaInfoPedNota($senha, $loja, $chave, $ped, $cgc);
            //Log::info('log', ['message' => 'Ação: setMagCriarPedido // OK -> Cliente Localizado!!!']);
            return $dados;
        }catch (\SoapFault $e){
            Log::error('action.failed', ['message' => 'Error: '.$e->getMessage().' // NOK -> Cliente nao Localizado.']);
            return false;
        }
    }

}
