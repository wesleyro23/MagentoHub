@extends('layout.principal')

@section('content')

    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Relatório de Itens Sem Imagem</a>
        </li>
    </ol>

    <!-- DataTables Example -->
    <div class="card mb-3">
        <div class="card-header">
            <i class="fas fa-table"></i>
            Itens sem Imagem
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>SKU</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($dados as $item)
                        <tr>
                            <td>{{ $item or old('sku') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="90">Não Existem Itens sem Imagem</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection