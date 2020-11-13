@extends('layout.principal')

@section('content')

    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Relatório de Processos</a>
        </li>
    </ol>

    <!-- DataTables Example -->
    <div class="card mb-3">
        <div class="card-header">
            <i class="fas fa-cogs"></i>
            Processos
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        <th>Processos</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($ids as $item)
                        <tr>
                            <td>{{ $item or old('ids') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="90">Não Existem Processos</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection