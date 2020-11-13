@extends('layout.principal')

@section('content')

    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Produtos Funcionários</a>
        </li>
    </ol>

    <div class="card mb-3">
        <div class="card-header">
            <i class="fas fa-table"></i>
            Comunica Envio de Produto dos Funcionários
        </div>
        <div class="card-body">
            <form id="formComunica" method="POST" action="{{ url("/formEnviaProdFunc") }}">
                {{ csrf_field() }}
                <div class="form-group">
                    <label for="chavenfe">Chave-NF:</label>
                    <input type="text" name="chavenfe" class="form-control" id="chavenfe" size="45" autofocus placeholder="Chave-Nfe">
                </div>
            </form>
        </div>
    </div>

                    <div>
                        @if(isset($insert))
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>numpedido</th>
                                    <th>numserie</th>
                                    <th>numnotafiscal</th>
                                    <th>datanotafiscal</th>
                                    <th>msgassist</th>
                                </tr>
                                </thead>

                                <tbody>
                                @forelse($insert as $item)
                                    <tr>
                                        <td>{{ $item->numpedido }}</td>
                                        <td>{{ $item->numserie }}</td>
                                        <td>{{ $item->numnotafiscal }}</td>
                                        <td>{{ $item->datanotafiscal }}</td>
                                        <td>{{ $item->msgassist }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="90">Não Existem Itens!!!</td>
                                    </tr>
                                @endforelse
                                </tbody>

                            </table>

                        @endif
                    </div>


            </div>
        </div>
    </div>
    <script>

        $(document).ready(function(){

            $("input").change(function (event) {
                this.form.submit();

            });

            $('input').focus();

        });

    </script>

@endsection
