@extends('layout.principal')

@section('content')

    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

        <h1 class="page-header">Parâmetros</h1>

        <div class="row placeholders">
            <div class="col-xs-12 col-sm-6 placeholder">

                @if( isset($parametros))
                    <form class="form-horizontal" method="POST" action="{{ url("/configuracoes/editparam/$parametros->id") }}">
                @else
                    <form class="form-horizontal" method="POST" action="{{ url("/configuracoes/cadparam") }}">
                @endif
                        {{ csrf_field() }}

                        <div class="form-group">
                            <label for="inputLoginMagento" class="col-sm-4 control-label">Login Magento</label>
                            <div class="col-sm-8">
                                <input type="text" name="login_magento" value="{{ $parametros->login_magento or old('login_magento') }}" class="form-control" id="inputLoginMagento" placeholder="Login Magento">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputSenhaMagento" class="col-sm-4 control-label">Senha Magento</label>
                            <div class="col-sm-8">
                                <input type="text" name="senha_magento" value="{{ $parametros->senha_magento or old('senha_magento') }}" class="form-control" id="inputSenhaMagento" placeholder="Senha Magento">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputconexaoMagento" class="col-sm-4 control-label">Conexão Magento</label>
                            <div class="col-sm-8">
                                <input type="text" name="conexaoMagento" value="{{ $parametros->conexaoMagento or old('conexaoMagento') }}" class="form-control" id="inputconexaoMagento" placeholder="Conexão Magento">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputTipoCadprodMagento" class="col-sm-4 control-label">Tipo Cadprod Magento</label>
                            <div class="col-sm-8">
                                <input type="text" name="tipo_cadprod_magento" value="{{ $parametros->tipo_cadprod_magento or old('tipo_cadprod_magento') }}" class="form-control" id="inputTipoCadprodMagento" placeholder="Tipo Cadprod Magento">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputAttributeSetListMagento" class="col-sm-4 control-label">Attribute SetList Magento</label>
                            <div class="col-sm-8">
                                <input type="text" name="attributeSetList_magento" value="{{ $parametros->attributeSetList_magento or old('attributeSetList_magento') }}" class="form-control" id="inputAttributeSetListMagento" placeholder="Attribute SetList Magento">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputStoreViewMagento" class="col-sm-4 control-label">StoreView Magento</label>
                            <div class="col-sm-8">
                                <input type="text" name="store_view_magento" value="{{ $parametros->store_view_magento or old('store_view_magento') }}" class="form-control" id="inputStoreViewMagento" placeholder="StoreView Magento">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputLoja" class="col-sm-4 control-label">Loja</label>
                            <div class="col-sm-8">
                                <input type="text" name="loja" value="{{ $parametros->loja or old('loja') }}" class="form-control" id="inputLoja" placeholder="Loja">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputTabelaPrecoErp" class="col-sm-4 control-label">Tabela Preco Erp</label>
                            <div class="col-sm-8">
                                <input type="text" name="tabela_preco_erp" value="{{ $parametros->tabela_preco_erp or old('tabela_preco_erp') }}" class="form-control" id="inputTabelaPrecoErp" placeholder="Tabela Preco Erp">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputLoginErp" class="col-sm-4 control-label">Login Erp</label>
                            <div class="col-sm-8">
                                <input type="text" name="login_erp" value="{{ $parametros->login_erp or old('login_erp') }}" class="form-control" id="inputLoginErp" placeholder="Login Erp">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputSenhaErp" class="col-sm-4 control-label">Senha Erp</label>
                            <div class="col-sm-8">
                                <input type="text" name="senha_erp" value="{{ $parametros->senha_erp or old('senha_erp') }}" class="form-control" id="inputSenhaErp" placeholder="Senha Erp">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputconexaoDatasul" class="col-sm-4 control-label">Conexão Datasul</label>
                            <div class="col-sm-8">
                                <input type="text" name="conexaoDatasul" value="{{ $parametros->conexaoDatasul or old('conexaoDatasul') }}" class="form-control" id="inputconexaoDatasul" placeholder="Conexão Datasul">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputCodigoTransportador" class="col-sm-4 control-label">Codigo Transportador</label>
                            <div class="col-sm-8">
                                <input type="text" name="codigo_transportador" value="{{ $parametros->codigo_transportador or old('codigo_transportador') }}" class="form-control" id="inputCodigoTransportador" placeholder="Codigo Transportador">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputStatusPedidoIntegracao" class="col-sm-4 control-label">Status Pedido Integracao</label>
                            <div class="col-sm-8">
                                <input type="text" name="status_pedido_integracao" value="{{ $parametros->status_pedido_integracao or old('status_pedido_integracao') }}" class="form-control" id="inputStatusPedidoIntegracao" placeholder="Status Pedido Integracao">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputTipoConsskuMagento" class="col-sm-4 control-label">Tipo Conssku Magento</label>
                            <div class="col-sm-8">
                                <input type="text" name="tipo_conssku_magento" value="{{ $parametros->tipo_conssku_magento or old('tipo_conssku_magento') }}" class="form-control" id="inputTipoConsskuMagento" placeholder="Tipo Conssku Magento">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmailRecebePedidos" class="col-sm-4 control-label">Email Recebe Pedidos</label>
                            <div class="col-sm-8">
                                <input type="text" name="email" value="{{ $parametros->email or old('email') }}" class="form-control" id="inputEmailRecebePedidos" placeholder="Email Recebe Pedidos">
                            </div>
                        </div>


                        <div class="form-group">
                            <input type="submit" name="enviar" value="Enviar" class="btn btn-default" />
                        </div>

                    </form>

            </div>
        </div>

    </div>

@endsection



