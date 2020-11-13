<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Magento\MagentoController;
use App\Jobs\Job;
use App\Models\Magento\AssociativeEntity;
use App\Models\Magento\CatalogProductRequestAttributes;
use App\Models\Parametro;
use App\Models\Pedido;
use Faker\Provider\DateTime;
use Illuminate\Http\Request;

use App\Http\Requests;
use Excel;
use Illuminate\Support\Facades\DB;
use Symfony\Component\VarDumper\Dumper\DataDumperInterface;

class RelatoriosController extends Controller
{
    protected $objMagento;
    protected $parametros;

    public function __construct(MagentoController $objMagento, Parametro $parametros)
    {
        $this->objMagento = $objMagento;
        $this->parametros = $parametros;
    }


    public function relItensIncompletos()
    {
        $parametros = $this->parametros->all();

        //Define parametros
        foreach ($parametros as $parametro){
            $loginMagento = $parametro->login_magento;
            $senhaMagento = $parametro->senha_magento;
            $storeViewMagento = $parametro->store_view_magento;
            $tipoConsskuMagento = $parametro->tipo_conssku_magento;
        }

        //Recupera a sessao Magento
        $sessaoMagento = $this->objMagento->getSessao($loginMagento,$senhaMagento);

        $produtosMagento = $this->objMagento->getListItemMagento($sessaoMagento);

        $atributos = new CatalogProductRequestAttributes();

        $atributos->setAdditionalAttributes(array(
            /*Peso e Dimensões*/
            'volume_altura',
            'volume_largura',
            'volume_comprimento',
            'volume_altura_produto',
            'volume_largura_produto',
            'volume_comprimento_produto',
            /*Atributos do Produto*/
            'computer_manufacturers',
            'modelo',
            'manufacturer',
            'tensao',
            'color',
            'tempo_de_garantia',
            'caracteristicas',
            /*Preço Avançado*/
            'special_price',
            /*Atributos Anymarket*/
            'integra_anymarket',
            'titulo_anymarket',
            'origem_anymarket',
            'markup',
            'calculo_preco',
            'codigo_barra_produto',
            'codigo_ncm'
        ));

        //Monta uma lista com todos os itens cadastrados no magento
        $listProducts = array();
        foreach ($produtosMagento as $item){
            $listProducts[] = $item->sku;
        }

        //Só retorna no relatório itens com saldo em estoque
        $saldoItens = $this->objMagento->catalogInventoryStockItemList($sessaoMagento,$listProducts);

        $dados = new \ArrayObject();

        foreach ($saldoItens as $item){

            if ($item->qty <> 0) {

                $produtoMagento = $this->objMagento->getItemMagentoBySKU($sessaoMagento,$item->sku,$storeViewMagento,$atributos,$tipoConsskuMagento);

                for ($i = 0; $i < count($produtoMagento->additional_attributes); $i++){

                    $arrAttrib[$produtoMagento->additional_attributes[$i]->key] = $produtoMagento->additional_attributes[$i]->value;

                }

                $prod = (array)$produtoMagento + $arrAttrib;

                if (
                    /*Geral*/
                    empty($prod['name']) || empty($prod['price']) || empty($prod['description']) || empty($prod['short_description'])
                    /*Peso e Dimensões*/
                    || empty($prod['weight']) || empty($prod['volume_altura']) || empty($prod['volume_largura']) || empty($prod['volume_comprimento'])
                    || empty($prod['volume_altura_produto']) || empty($prod['volume_largura_produto']) || empty($prod['volume_comprimento_produto'])
                    /*Atributos do Produto*/
                    || empty($prod['computer_manufacturers']) || empty($prod['manufacturer']) || empty($prod['modelo'])
                    || empty($prod['tensao'])
                    || empty($prod['color']) || empty($prod['tempo_de_garantia']) || empty($prod['caracteristicas'])
                    /*Preço Avançado*/
                    || empty($prod['special_price'])
                    /*Mecanismos de Busca (SEO)*/
                    || empty($prod['url_key']) || empty($prod['meta_title']) || empty($prod['meta_keyword']) || empty($prod['meta_description'])
                    /*Atributos Anymarket*/
                    || empty($prod['titulo_anymarket']) ||  empty($prod['origem_anymarket']) || empty($prod['markup'])
                    || empty($prod['calculo_preco']) || empty($prod['codigo_barra_produto']) || empty($prod['codigo_ncm'])
                    || $prod['integra_anymarket'] == 0
                    /*Categorias do Produto*/
                    || empty($prod['categories'])
                ) {
                    $dados->append($prod);
                }

            }

        }

        return view('relatorios.relItensIncompletos', compact('dados'));

    }

    public function relItensSemImg()
    {
        $parametros = Parametro::all();

        //Define parametros
        foreach ($parametros as $parametro){
            $loginMagento = $parametro->login_magento;
            $senhaMagento = $parametro->senha_magento;
            $typeProdMagento = $parametro->tipo_cadprod_magento;
            $atribListMagento = $parametro->attributeSetList_magento;
            $storeViewMagento = $parametro->store_view_magento;
            $tabelaPrecoErp = $parametro->tabela_preco_erp;
            $grupoTabelasPrecoDatasul = $parametro->grupo_tabelas_parceiros;
            $tipoConsskuMagento = $parametro->tipo_conssku_magento;
        }

        //Monta objeto Magento para acesso a metodos
        $objMagento = new MagentoController();

        //Recupera a sessao Magento
        $sessaoMagento = $objMagento->getSessao($loginMagento,$senhaMagento);

        $produtosMagento = $objMagento->getListItemMagento($sessaoMagento);

        $dados = new \ArrayObject();

        foreach ($produtosMagento as $item){

            $mediaList = $objMagento->catalogProductAttributeMediaList($sessaoMagento,$item->sku,$storeViewMagento,$tipoConsskuMagento);

            if ($mediaList == null){
                $dados->append($item->sku);
                //$listSemImg[] = $item->sku;
            }

        }

        //dd($dados);

        return view('relatorios.relItensSemImg', compact('dados'));

    }

    public function processos()
    {
        $jobs = DB::table('jobs')->get();

        for ($i = 0; $i < count($jobs); $i++){

            $ids[] = $jobs[$i]->id;

        }

        if (empty($ids))
            $ids = [];
        
        return view('relatorios.processos', compact('ids'));
    }

    public function getRelPedidos()
    {
        return view('relatorios.relPedidos');
    }

    public function getRelPedidosJson($status,$dataIni, $dataFim)
    {
        $dtini = new \DateTime($dataIni);
        $diaFim = substr($dataFim,8,2);
        $mesFim = substr($dataFim,5,2);
        $anoFim = substr($dataFim,0,4);

        $parametros = $this->parametros->all();

        //Define parametros
        foreach ($parametros as $parametro){
            $loginMagento = $parametro->login_magento;
            $senhaMagento = $parametro->senha_magento;
            $storeViewMagento = $parametro->store_view_magento;
            $tipoConsskuMagento = $parametro->tipo_conssku_magento;
        }

        //Recupera a sessao Magento
        $sessaoMagento = $this->objMagento->getSessao($loginMagento,$senhaMagento);

        /* array("from"=>$fromValue, "to"=>$toValue) - array("like"=>$como) - array("neq"=>$naoIgual) - array("in"=>array($comOsValores)) - array("nin"=>array($semOsValores))
           array("eq"=>$igual) - array("nlike"=>$nãoComo) - array("is"=>$is ) - array("gt"=>$maiorQue) - array("lt"=>$menorQue) - array("gteq"=>$MaioOuIgualQue)
           array("lteq"=>$menorOuIgualQue) - array("finset"=>$unknown ) */

        //Filtro para Pedido
        $filtro = new \stdClass();

        $aeFilter = new AssociativeEntity();
        $aeFilter->setKey('status');
        $aeFilter->setValue($status);
        $filtro->filter[] = $aeFilter;

        $aeComplexFilter1 = new AssociativeEntity();
        $aeComplexFilter1->setKey('created_at');
        $aeComplexFilter1->setValue([
            'key' =>'from',
            'value' => $dtini->format('Y-m-d H:i:s')
        ]);
        $filtro->complex_filter[] = $aeComplexFilter1;

        $aeComplexFilter2 = new AssociativeEntity();
        $aeComplexFilter2->setKey('created_at');
        $aeComplexFilter2->setValue([
            'key' =>'to',
            'value' => date("Y-m-d H:i:s", mktime(23,59,59, $mesFim,$diaFim,$anoFim))
        ]);
        $filtro->complex_filter[] = $aeComplexFilter2;

        $pedidos = $this->objMagento->getSalesOrderList($sessaoMagento, $filtro);

        $listaPedidos = array();

        foreach ($pedidos as $pedido){

            $pedidoMagento = $this->objMagento->getSalesOrderInfo($sessaoMagento,$pedido->increment_id);

            $listaPedidos[] = $pedidoMagento;

        }

        //dd($listaPedidos);

        return response()->json($listaPedidos);
    }
}
