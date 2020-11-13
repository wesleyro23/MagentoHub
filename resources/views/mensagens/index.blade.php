@extends('layout.principal')

@section('content')

    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Integração {{$titulo}}</a>
        </li>
    </ol>

    <!-- DataTables Example -->
    <div class="card mb-3">
        <div class="card-header">
            <i class="fas fa-table"></i>
            {{$titulo}}
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Retorno</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($dados as $item)
                        <tr>
                            <td>{{ $item->item }}</td>
                            <td>{{ $item->mes }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="90">Não Existem Itens Cadastrados ou Atualizados</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
