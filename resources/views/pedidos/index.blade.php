@extends('layout.principal')

@section('content')

    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Integração de Pedidos</a>
        </li>
    </ol>

    @if( isset($dados) && count($dados) > 0 )
        <div class="alert alert-danger">
                {{ $dados->cMsg }} <br>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header">
            <i class="fas fa-table"></i>
            Pedidos para Integrar
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>numero_pedido_magento</th>
                        <th>sincronizado_datasul</th>
                        <th>sincronizado_magento</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($pedidosIntegra as $pedido)
                        <tr>
                            <td>{{ $pedido->numero_pedido_magento }}</td>
                            <td>{{ $pedido->sincronizado_datasul }}</td>
                            <td>{{ $pedido->sincronizado_magento }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="90">Não Existem Pedidos para serem Processados</td>
                        </tr>
                    @endforelse
                    </tbody>

                </table>
                <a class="btn btn-success" href="{{ url("/pedidosdatasulintegra") }}" >
                    <i class="fas fa-cloud-download-alt"></i>
                    Integra Datasul
                </a>
            </div>
        </div>
    </div>

@endsection



{{--
numero_pedido_magento 	numero_pedido_datasul Ascendente 1 	sincronizado_datasul 	sincronizado_magento 	pedido_valido_magento
--}}