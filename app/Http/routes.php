<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['prefix' => '/', 'middleware' => ['auth'], 'web'], function($route){

    //Rota Configurações Gerais
    $route->get('/configuracoes', 'ConfiguracoesController@index');
    //Rotas Parametros
    $route->get('/configuracoes/cadparam', 'ConfiguracoesController@cadParam');
    $route->post('/configuracoes/cadparam', 'ConfiguracoesController@cadParamGo');
    $route->get('/configuracoes/editparam/{id}', 'ConfiguracoesController@editParam');
    $route->post('/configuracoes/editparam/{id}', 'ConfiguracoesController@editParamGo');

    //Rotas Transportadoras
    $route->get('/configuracoes/cadtransp', 'ConfiguracoesController@cadTransp');
    $route->post('/configuracoes/cadtransp', 'ConfiguracoesController@cadTranspGo');
    $route->get('/configuracoes/edittransp/{id}', 'ConfiguracoesController@editTransp');
//    $route->post('/configuracoes/edittransp/{id}', 'ConfiguracoesController@editTranspGo');

    //Rota form Produtos Funcionarios
    $route->get('/formEnviaProdFunc', 'TransportadorasController@getFormEnviaProdFunc');
    $route->post('/formEnviaProdFunc', 'TransportadorasController@postFormEnviaProdFunc');

    //Rota integração e Carga de Itens
    $route->get('/itensintegra', 'ItensCargaController@integracao');
    $route->get('/itensintegrasaldo', 'ItensCargaController@integracaoSaldo');
    $route->post('/itenscargacsv', 'ItensCargaController@itensCargaCsv');

    //Rotas Integração de Pedidos
    $route->get('/pedidosintegra', 'PedidosCargaController@integracao');
    $route->get('/pedidosdatasulintegra', 'PedidosCargaController@pedidosDatasulIntegracao');

    $route->get('/formComunicaFaturamento', 'PedidosCargaController@getFormComunicaFaturamento');
    $route->post('/formComunicaFaturamento', 'PedidosCargaController@postFormComunicaFaturamento');

    //Rotas para relatorios
    $route->get('/relitensincompletos', 'RelatoriosController@relItensIncompletos');
    $route->get('/relitenssemimg', 'RelatoriosController@relItensSemImg');
    $route->get('/processos','RelatoriosController@processos');
    $route->get('/relpedidos','RelatoriosController@getRelPedidos');
    $route->get('/relpedidosjson/{status}/{dataIni}/{dataFim}','RelatoriosController@getRelPedidosJson');

    $route->get('/', 'SiteController@index');

});

//Web Service Expediçao
//http://192.168.0.23:8180/MagentoHub/enviaItens?expedItemJson={"expedItem":{"nrPedidoDatasul":"MG100000013","chaveXml":"232555144848484151455454578","Items":[{"codigoItem":"220SEC87E","qtd":1,"Rastremantos":["ttt2222","tttt33333"]}]}}
Route::get('/enviaItens', 'ExpedItemController@getEnviaItens');
Route::get('/enviaRastreio', 'ExpedItemController@getEnviaRastreio');
Route::get('/entrega', 'ExpedItemController@getEntrega');

Route::get('/buscaRastreioTransportador','TransportadorasController@getBuscaRastreioTransportador');
Route::get('/enviaRastreioTransp','TransportadorasController@getEnviaRastreioTransp');
Route::get('/entregaTransportador','TransportadorasController@getEntregaTransportador');

Route::get('/statusfaturado', 'PedidosCargaController@statusFaturado');


//Rota de autenticação
Route::auth();


