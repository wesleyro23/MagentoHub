@extends('layout.principal')

@section('content')

    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Transportadoas</a>
        </li>
    </ol>

    <div class="card mb-3">
        <div class="card-header">
            <i class="fas fa-table"></i>
            Manutenção Transportadoas
        </div>
        <div class="card-body">

            @if( isset($trasportadoras))
                <form class="form-horizontal" method="POST" action="{{ url("/configuracoes/edittransp/$trasportadoras->id") }}">
            @else
                <form class="form-horizontal" method="POST" action="{{ url("/configuracoes/cadtransp") }}">
            @endif
                    {{ csrf_field() }}

                    <div class="form-group">
                        <label for="inputCodigoMagento" class="col-sm-4 control-label">Código Magento</label>
                        <div class="col-sm-6">
                            <input type="text" name="codigo_magento" value="{{ $trasportadoras->codigo_magento or old('codigo_magento') }}" class="form-control" id="inputCodigoMagento" placeholder="Código Magento">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputCodigoDatasul" class="col-sm-4 control-label">Código Datasul</label>
                        <div class="col-sm-6">
                            <input type="text" name="codigo_datasul" value="{{ $trasportadoras->codigo_datasul or old('codigo_datasul') }}" class="form-control" id="inputCodigoDatasul" placeholder="Código Datasul">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-6">
                            <input type="submit" name="enviar" value="Enviar" class="btn btn-default">
                        </div>
                    </div>
                </form>
        </div>
    </div>


@endsection



