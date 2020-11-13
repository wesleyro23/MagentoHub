@extends('layout.principal')

@section('content')

    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Configurações</a>
        </li>
    </ol>

    <!-- DataTables Example -->
    <div class="card mb-3">
        <div class="card-header">
            <i class="fas fa-table"></i>
            Parâmetros
            <a class="btn btn-success" href="{{ url('/configuracoes/cadparam') }}" role="button">
                <i class="fas fa-plus"></i>
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Login Magento</th>
                            <th>Senha Magento</th>
                            <th>WS Magento</th>
                            <th>Tipo Cadastro Magento</th>
                            <th>attributeSetList_magento</th>
                            <th>store_view_magento</th>
                            <th>Loja</th>
                            <th>Tabela Preço ERP</th>
                            <th>Login ERP</th>
                            <th>Senha ERP</th>
                            <th>WS Datasul</th>
                            <th>codigo_transportador</th>
                            <th>Status Pedido Magento</th>
                            <th>Tipo Consulta Magento</th>
                            <th>E-mail</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse( $parametros as $parametro )
                        <tr>
                            <td>{{ $parametro->login_magento }}</td>
                            <td>{{ $parametro->senha_magento }}</td>
                            <td>{{ $parametro->conexaoMagento }}</td>
                            <td>{{ $parametro->tipo_cadprod_magento }}</td>
                            <td>{{ $parametro->attributeSetList_magento }}</td>
                            <td>{{ $parametro->store_view_magento }}</td>
                            <td>{{ $parametro->Loja }}</td>
                            <td>{{ $parametro->tabela_preco_erp }}</td>
                            <td>{{ $parametro->login_erp }}</td>
                            <td>{{ $parametro->senha_erp }}</td>
                            <td>{{ $parametro->conexaoDatasul }}</td>
                            <td>{{ $parametro->codigo_transportador }}</td>
                            <td>{{ $parametro->status_pedido_integracao }}</td>
                            <td>{{ $parametro->tipo_conssku_magento }}</td>
                            <td>{{ $parametro->email }}</td>
                            <td>
                                <a class="btn btn-success" href="{{ url("/configuracoes/editparam/$parametro->id") }}" role="button">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a class="btn btn-danger" href="{{ url("/configuracoes/delparam/$parametro->id") }}" role="button">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="90">Não Existem Parâmatros Cadastrados</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{--########################################################################################--}}

    <!-- DataTables Example -->
    <div class="card mb-3">
        <div class="card-header">
            <i class="fas fa-table"></i>
            Transportadoras
            <a class="btn btn-success" href="{{ url('/configuracoes/cadtransp') }}" role="button">
                <i class="fas fa-plus"></i>
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">

                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        <th>Código Magento</th>
                        <th>Código Datasul</th>
                        <th>Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse( $trasportadoras as $trasportadora )
                        <tr>
                            <td>{{ $trasportadora->codigo_magento }}</td>
                            <td>{{ $trasportadora->codigo_datasul }}</td>
                            <td>
                                <a class="btn btn-success" href="{{ url("/configuracoes/editparam/$parametro->id") }}" role="button">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a class="btn btn-danger" href="{{ url("/configuracoes/delparam/$parametro->id") }}" role="button">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="90%">Não Existem Parâmatros Cadastrados</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <h1 class="page-header">Caraga de Itens Csv</h1>
        <div class="row placeholders">
            <div class="col-xs-12 col-sm-6 placeholder">

                <form class="form-horizontal" method="post" action="{{ url("/itenscargacsv") }}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label for="exampleInputFile" class="col-sm-2 control-label">ARQUIVO CSV</label>
                        <div class="col-sm-10">
                            <input type="file" name="file" id="exampleInputFile">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-default">Enviar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>


@endsection
