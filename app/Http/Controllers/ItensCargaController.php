<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateItemMagentoBySKU;
use App\Jobs\UpdateSaldoItemMagentoBySku;
use Log;
use App\Http\Controllers\Datasul\DatasulController;
use App\Http\Controllers\Magento\MagentoController;
use App\Models\Magento\AssociativeEntity;
use App\Models\Magento\CatalogInventoryStockItemUpdateEntity;
use App\Models\Magento\CatalogProductAdditionalAttributesEntity;
use App\Models\Magento\CatalogProductCreateEntity;
use App\Models\Magento\CatalogProductGroupPriceEntity;
use App\Models\Parametro;
use Illuminate\Http\Request;

use App\Http\Requests;
use Maatwebsite\Excel\Facades\Excel;
use PhpParser\Node\Expr\Print_;


class ItensCargaController extends Controller
{
    protected $request;
    protected $objMagento;
    protected $parametros;
    protected $objDatasul;

    public function __construct(Request $request, MagentoController $objMagento, DatasulController $objDatasul,Parametro $parametros)
    {
        $this->request = $request;
        $this->objMagento = $objMagento;
        $this->objDatasul = $objDatasul;
        $this->parametros = $parametros;
    }

    public function integracao()
    {
        $parametros = $this->parametros->all();

        //Define parametros
        foreach ($parametros as $parametro){
            $loja = $parametro->Loja;
            $loginMagento = $parametro->login_magento;
            $senhaMagento = $parametro->senha_magento;
            $typeProdMagento = $parametro->tipo_cadprod_magento;
            $atribListMagento = $parametro->attributeSetList_magento;
            $storeViewMagento = $parametro->store_view_magento;
            $tabelaPrecoErp = $parametro->tabela_preco_erp;
            $tipoConsskuMagento = $parametro->tipo_conssku_magento;
        }

        //Recupera a sessao Magento
        $sessaoMagento = $this->objMagento->getSessao($loginMagento,$senhaMagento);

        //Checar se a Tabela X Grupo já está cridada no magento
        $grupoParceirosMagento = $this->objMagento->getCustomerGroupList($sessaoMagento);

        //Passa parametros para o metodo de integração dos itens
        $dados = $this->integraItemMagento($sessaoMagento,$typeProdMagento,$atribListMagento,$storeViewMagento,$grupoParceirosMagento,$tabelaPrecoErp,$tipoConsskuMagento,$loja);

        if ($dados) {
            $integraItens = 'C:\xampp\htdocs\MagentoHub\integraItens.bat 2>/dev/null >/dev/null &';
            shell_exec($integraItens);
        }

        $titulo = "Item";

        return view('mensagens.index', compact('dados', 'titulo'));
        //if($enviaItemMagento == true){
        //    return redirect("/");
        //}

    }

    public function integraItemMagento($sessaoMagento,$typeProdMagento,$atribListMagento,$storeViewMagento,$grupoParceirosMagento,$tabelaPrecoErp,$tipoConsskuMagento,$loja)
    {
        //Guarda a lista de skus da tabela de preço do datasul
        Log::info('log', ['message' => "TABELA PRECO: ".$tabelaPrecoErp]);
        $listaItemDatasul = $this->objDatasul->getListaItensDatasulByTabelaPreco($tabelaPrecoErp,$loja);

//        dd($listaItemDatasul);

        //Verifica se a lista está vazia
        if (sizeof($listaItemDatasul) > 0){

            foreach ($listaItemDatasul->ttProdutos as $itemDatasul) {
                Log::info('log', ['message' => "Enviando Item: ".$itemDatasul->ItemCodigo]);
                $itemMagento = $this->convertItemDatasulItemMagento($itemDatasul,$grupoParceirosMagento);

//                dd($itemMagento);

                if ($this->objMagento->getItemMagentoBySKU($sessaoMagento,$itemDatasul->ItemCodigo,$storeViewMagento,$atribListMagento,$tipoConsskuMagento) == null){
                    Log::info('log', ['message' => "Item não existe no Magento, criando."]);
                    $createProduct = $this->objMagento->createItemMagentoBySKU($sessaoMagento,$typeProdMagento,$atribListMagento,$itemDatasul->ItemCodigo,$itemMagento,$storeViewMagento);

                    $itens = new \stdClass();
                    $itens->item = $itemDatasul->ItemCodigo;
                    $itens->mes  = 'Create';

                    $retorno[] = $itens;

                } else{

                    Log::info('log', ['message' => "Item existe no Magento, atualizando..."]);
                    //$updateProduct = $objMagento->updateItemMagentoBySKU($sessaoMagento,$itemDatasul->ItemCodigo,$itemMagento, $storeViewMagento, $tipoConsskuMagento);

                    $this->dispatch(
                        new UpdateItemMagentoBySKU($sessaoMagento,$itemDatasul->ItemCodigo,$itemMagento, $storeViewMagento, $tipoConsskuMagento)
                    );

                    /*
                     * teste
                     *
                     * if ($itemDatasul->ItemCodigo == "110CXA05N"){
                        dd($itemMagento);
                    }
                     * */

                    $itens = new \stdClass();
                    $itens->item = $itemDatasul->ItemCodigo;
                    $itens->mes  = 'Update';

                    $retorno[] = $itens;

                }
            }
        }

        return $retorno;
    }

    public function convertItemDatasulItemMagento($itemDatasul, $grupoParceirosMagento)
    {

        $itemMag = new \stdClass();

//        $itemMag->name = ucwords(mb_strtolower($itemDatasul->DescItem,'UTF-8'));
        $itemMag->weight = $itemDatasul->Peso;
        $itemMag->price = $itemDatasul->PrecoNormal;
        $itemMag->special_price = $itemDatasul->PrecoMinimo;

        $stock_data = new \stdClass();
        $stock_data->min_qty = 1;
        /*
         * Força saldo zero
         * */
        //$stock_data->qty = 0;
        //$itemMag->stock_data = $stock_data;

        /*****************TRATAMENTO URL KEY*******************/
        /**
         * Converte a String para ASCII
         * O //TRANSLIT irá tentar traduzir os caracteres, por exemplo è => "`e"
         * Após isso, aplicamos uma expressão regular para deixar somente \w = Números, Letras e "underline"; e \s = espaço
         */
        $urlkey = preg_replace("/[^\w\s]/", "", iconv("UTF-8", "ASCII//IGNORE", $itemDatasul->DescItem));
        /**
         * Com o str_replace podemos substituir os espaços deixados na linha anterior, pelo hífen
         */
        $urlkey = str_replace(" ", "-", $urlkey);
        /**
         * Transformamos todo o texto em minúsculo
        */
        $urlkey = strtolower($urlkey);
        /*****************TRATAMENTO URL KEY*******************/

        $itemMag->url_key = $urlkey;
        $itemMag->url_path = $urlkey; //$itemDatasul->ItemCodigo;
        $itemMag->meta_description = $itemDatasul->DescItem;
        $itemMag->meta_keyword = $itemDatasul->ItemCodigo." ".$itemDatasul->DescItem;
        $itemMag->meta_title = $itemDatasul->DescItem;

        $adicionalAtributo = new CatalogProductAdditionalAttributesEntity();

        //Marca produto como inporta Anymarket
        /*$integraAny = new AssociativeEntity();
        $integraAny->setKey('integra_anymarket');
        $integraAny->setValue(1);
        $adicionalAtributo->setSingleData($integraAny);*/

        //volume_altura
        $va = new AssociativeEntity();
        $va->setKey('volume_altura');
        $va->setValue(ceil($itemDatasul->altura));
        $adicionalAtributo->setSingleData($va);

        //volume largura
        $vl = new AssociativeEntity();
        $vl->setKey('volume_largura');
        $vl->setValue(ceil($itemDatasul->largura));
        $adicionalAtributo->setSingleData($vl);

        //volume_comprimento
        $vc = new AssociativeEntity();
        $vc->setKey('volume_comprimento');
        $vc->setValue(ceil($itemDatasul->comprimento));
        $adicionalAtributo->setSingleData($vc);

        //volume_altura_produto scx
        $vap = new AssociativeEntity();
        $vap->setKey('volume_altura_produto');
        $vap->setValue(ceil($itemDatasul->altura_scx));
        $adicionalAtributo->setSingleData($vap);

        //volume_largura_produto scx
        $vlp = new AssociativeEntity();
        $vlp->setKey('volume_largura_produto');
        $vlp->setValue(ceil($itemDatasul->largura_scx));
        $adicionalAtributo->setSingleData($vlp);

        //volume_comprimento_produto scx
        $vcp = new AssociativeEntity();
        $vcp->setKey('volume_comprimento_produto');
        $vcp->setValue(ceil($itemDatasul->comprimento_scx));
        $adicionalAtributo->setSingleData($vcp);

        //Marca produto como inporta Anymarket
        $integraFotos = new AssociativeEntity();
        $integraFotos->setKey('integra_images_root_anymarket');
        $integraFotos->setValue(1);
        $adicionalAtributo->setSingleData($integraFotos);

        // Cadastrando Marca dos Produtos
        $marca = new AssociativeEntity();
        $marca->setKey('computer_manufacturers');
        $marca->setValue('NKS');
        $adicionalAtributo->setSingleData($marca);

        // Cadastrando IPI do Item em um Atributo Adicional no Magento
        $ipi = new AssociativeEntity();
        $ipi->setKey('aliquota_ipi');
        $ipi->setValue($itemDatasul->IpiItem);
        $adicionalAtributo->setSingleData($ipi);

        //Titulo que irá sincronizar com os Marketplaces $itemMag->name = ;
        $tit = new AssociativeEntity();
        $tit->setKey('titulo_anymarket');
        $tit->setValue(ucfirst(mb_strtolower($itemDatasul->DescItem,'UTF-8')));
        $adicionalAtributo->setSingleData($tit);

        // Cadastrando Origem da Mercadoria
        $ori = new AssociativeEntity();
        $ori->setKey('origem_anymarket');
        $ori->setValue($itemDatasul->origem);
        $adicionalAtributo->setSingleData($ori);

        // Cadastrando NBM da Mercadoria
        $ncm = new AssociativeEntity();
        $ncm->setKey('codigo_ncm');
        $ncm->setValue($itemDatasul->ncm);
        $adicionalAtributo->setSingleData($ncm);

        // Cadastrando Codigo de Barras da Mercadoria
        $bar = new AssociativeEntity();
        $bar->setKey('codigo_barra_produto');
        $bar->setValue($itemDatasul->CodBar);
        $adicionalAtributo->setSingleData($bar);

        $itemMag->additional_attributes = $adicionalAtributo;

        //Grupo de Preço X Tabela de Preço
        if (!empty($itemDatasul->ttItemTabela)){

            Log::info('log', ['message' => "Tabelas no datasul: ".json_encode($itemDatasul->ttItemTabela)]);

            $grupoPreco[] = new CatalogProductGroupPriceEntity();

            for ($i = 0;  $i < count($itemDatasul->ttItemTabela); $i++){

                $controle = 0;
                if (count($grupoParceirosMagento) > 0 ){

                    for ($j = 0; $j < count($grupoParceirosMagento); $j++){

                        //Rever ficou uma Merda
                        if (count($itemDatasul->ttItemTabela) == 1){
                            $nomeTabDatasul = strtoupper($itemDatasul->ttItemTabela->nomeTabela);
                            $nomeTabMagento = strtoupper($grupoParceirosMagento[$j]->customer_group_code);
                            if ( $nomeTabDatasul == $nomeTabMagento){

                                Log::info('log', ['message' => "Tabela encontrada no Magento: ".$itemDatasul->ttItemTabela->nomeTabela." - Preço: ".$itemDatasul->ttItemTabela->precoTab]);
                                $precoGrupo = new CatalogProductGroupPriceEntity();
                                $precoGrupo->setCustGroup($grupoParceirosMagento[$j]->customer_group_id);
                                $precoGrupo->setWebsiteId("0");
                                $precoGrupo->setPrice($itemDatasul->ttItemTabela->precoTab);
                                $grupoPreco[$i] = $precoGrupo;
                                $controle = 1;
                            }
                        } else{
                            $nomeTabDatasul = strtoupper($itemDatasul->ttItemTabela[$i]->nomeTabela);
                            $nomeTabMagento = strtoupper($grupoParceirosMagento[$j]->customer_group_code);
                            if ( $nomeTabDatasul == $nomeTabMagento){

                                Log::info('log', ['message' => "Tabela encontrada no Magento: ".$itemDatasul->ttItemTabela[$i]->nomeTabela." - Preço: ".$itemDatasul->ttItemTabela[$i]->precoTab]);
                                $precoGrupo = new CatalogProductGroupPriceEntity();
                                $precoGrupo->setCustGroup($grupoParceirosMagento[$j]->customer_group_id);
                                $precoGrupo->setWebsiteId("0");
                                $precoGrupo->setPrice($itemDatasul->ttItemTabela[$i]->precoTab);
                                $grupoPreco[$i] = $precoGrupo;
                                $controle = 1;
                            }
                        }

                    }

                    if ($controle != 1){
                        if (count($itemDatasul->ttItemTabela) == 1){
                            Log::info('log', ['message' => "A tabela: ".json_encode($itemDatasul->ttItemTabela->nomeTabela).", não está cadastrada no MAGENTO!"]);
                        } else {
                            Log::info('log', ['message' => "A tabela: ".json_encode($itemDatasul->ttItemTabela[$i]->nomeTabela).", não está cadastrada no MAGENTO!"]);
                        }
                    }
                } else{
                    Log::info('log', ['message' => "Não existe nenhuma tabela/grupo de preços cadastrado no Magento."]);
                }
            }
            $itemMag->group_price = $grupoPreco;

        } else{
            Log::info('log', ['message' => "Item não possui tabela específica para grupos."]);
        }

        //Ativo ou Não
        if ($itemDatasul->Ativo == true){
            $itemMag->status = "1";
        } else{
            $itemMag->status = "2";
        }

        //Força itens como desativados
//        $itemMag->status = "2";

//        dd($itemMag);
        return $itemMag;

    }

    public function integracaoSaldo()
    {
        $parametros = $this->parametros->all();

        //Define parametros
        foreach ($parametros as $parametro){
            $loja = $parametro->Loja;
            $loginMagento = $parametro->login_magento;
            $senhaMagento = $parametro->senha_magento;
            $storeViewMagento = $parametro->store_view_magento;
            $tipoConsskuMagento = $parametro->tipo_conssku_magento;
        }

        //Recupera a sessao Magento
        $sessaoMagento = $this->objMagento->getSessao($loginMagento,$senhaMagento);

        $dados = $this->integraSaldoItemMagento($sessaoMagento, $loja, $storeViewMagento, $tipoConsskuMagento);

        $titulo = "Estoque";

        return view('mensagens.index', compact('dados', 'titulo'));

    }

    public function integraSaldoItemMagento($sessaoMagento, $loja, $storeViewMagento, $tipoConsskuMagento)
    {

        $listItensSaldoDatasul =  $this->objDatasul->getMagSaldoEstoque($loja,"");

//        dd($listItensSaldoDatasul->ttSaldo);

        $retorno = array();

        for ($i = 0; $i < count($listItensSaldoDatasul->ttSaldo); $i++){

            //Atualiza saldo Magento

            $itemMag = new \stdClass();

            $stock_data = new \stdClass();
            $stock_data->manage_stock = 1;
            $stock_data->qty = $listItensSaldoDatasul->ttSaldo[$i]->qtdDisp;
            $stock_data->is_in_stock = 1;
            $itemMag->stock_data = $stock_data;


            $this->dispatch(
                new UpdateSaldoItemMagentoBySku($sessaoMagento,$listItensSaldoDatasul->ttSaldo[$i]->itCodigo,$itemMag, $storeViewMagento, $tipoConsskuMagento)
            );

            $viewItens = new \stdClass();
            $viewItens->item = $listItensSaldoDatasul->ttSaldo[$i]->itCodigo;
            $viewItens->mes  = 'Atualizado saldo do Item no Magento, Qdt = '.$listItensSaldoDatasul->ttSaldo[$i]->qtdDisp;

            $retorno[] = $viewItens;

        }

        return $retorno;

    }

    public function itensCargaCsv()
    {
        //Recupera form
        $file = $this->request->file('file');

        //Define o caminho
        $path = public_path('assets/uploads');

        //Faz o upload da imagem
        $file->move($path, $file->getClientOriginalName());

        $arq = "public/assets/uploads/".$file->getClientOriginalName();

        $results = Excel::load($arq)->get();

        //Recupera paramatros do sistema
        $parametros = Parametro::all();

        //Define parametros
        foreach ($parametros as $parametro){
            $loginMagento = $parametro->login_magento;
            $senhaMagento = $parametro->senha_magento;
            $typeProdMagento = $parametro->tipo_cadprod_magento;
            $atribListMagento = $parametro->attributeSetList_magento;
            $storeViewMagento = $parametro->store_view_magento;
        }
        
        $parentId = 1;

        //Monta objeto Magento para acesso a metodos
        $objMagento = new MagentoController();

        //Recupera a sessao Magento
        $sessaoMagento = $objMagento->getSessao($loginMagento,$senhaMagento);

        //Arvore de categorias
        $catalogCategoryTree = $objMagento->getCatalogCategoryTree($sessaoMagento,$parentId,$storeViewMagento);
        
        //Recupera Produtos Já adastrados
        $prodSkus = $objMagento->getListItemMagento($sessaoMagento);

        $productData = new CatalogProductCreateEntity();

        foreach ($results as $result) {


            $controle = 0;

            foreach ($prodSkus as $prodSku){

                if($result->chcoditem == $prodSku->sku){
                    $controle = 1;
                }
            }

            if($controle == 0){
                
                $productData->setName($result->chdescitem);
                $productData->setDescription($result->chdescricaocomp);
                $productData->setShortDescription(mb_substr($result->chdescricaocomp,0,300)." ...");
                $productData->setStatus('2');
                $productData->setWeight($result->deembpeso);
                
                foreach ($catalogCategoryTree->children as $parent1){
                    foreach ($parent1->children as $categorias){
                        if ($result->chcategoria == $categorias->name){
                            $Categ = $categorias->category_id;
                            foreach ($categorias->children as $subCategorias){
                                if ($result->chsubcategoria == $subCategorias->name){
                                    $subCateg = $subCategorias->category_id;
                                }
                            }
                        }
                    }
                }

                $categSubcateg  = array();
                $categSubcateg[] = $Categ;
                $categSubcateg[] = $subCateg;
                $productData->setCategories($categSubcateg);

                $adicionalAtributo = new CatalogProductAdditionalAttributesEntity();
                // volume_largura
                $vl = new AssociativeEntity();
                $vl->setKey('volume_largura');
                $vl->setValue(ceil($result->deemblargura));
                $adicionalAtributo->setSingleData($vl);

                //volume_altura
                $va = new AssociativeEntity();
                $va->setKey('volume_altura');
                $va->setValue(ceil($result->deembaltura));
                $adicionalAtributo->setSingleData($va);

                //volume_comprimento
                $vc = new AssociativeEntity();
                $vc->setKey('volume_comprimento');
                $vc->setValue(ceil($result->deembprofund));
                $adicionalAtributo->setSingleData($vc);

                //caracteristicas
                $carac = new AssociativeEntity();
                $carac->setKey('caracteristicas');
                $carac->setValue($result->chcaracteristica);
                $adicionalAtributo->setSingleData($carac);

                //Modelo
                $mod = new AssociativeEntity();
                $mod->setKey('modelo');
                $mod->setValue($result->chmodelo);
                $adicionalAtributo->setSingleData($mod);


                //manufacturer
                $manufac = new AssociativeEntity();
                $manufac->setKey('manufacturer');
                if ($result->chnomefabricante == 'NKS'){
                    $manufac->setValue(160);
                }elseif ($result->chnomefabricante == 'Ford Home Solutions'){
                    $manufac->setValue(159);
                }elseif ($result->chnomefabricante == 'Milano'){
                    $manufac->setValue(158);
                }elseif ($result->chnomefabricante == 'Estrelas'){
                    $manufac->setValue(157);
                }elseif ($result->chnomefabricante == 'Excellence'){
                    $manufac->setValue(156);
                }elseif ($result->chnomefabricante == 'Blaupunkt'){
                    $manufac->setValue(155);
                }elseif ($result->chnomefabricante == 'Mais Você'){
                    $manufac->setValue(161);
                }
                //$adicionalAtributo = new CatalogProductAdditionalAttributesEntity();
                $adicionalAtributo->setSingleData($manufac);

                $productData->setAdditionalAttributes($adicionalAtributo);

            }

            $create = $objMagento->setCatalogProductCreate($sessaoMagento, $typeProdMagento, $atribListMagento, $result->chcoditem, $productData);

            //Adicionar um retorno para a view principal/////////////////////////////////////////
            if ($create){

                Log::info('log', ['message' => "Cadastros basicos efetivados do Item: ".$result->chcoditem]);
            } else{
                Log::info('log', ['message' => "Cadastros basicos não efetivados do Item: ".$result->chcoditem]);
            }
        }

        return redirect('/parametros');

    }
}
