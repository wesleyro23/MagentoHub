@extends('layout.principal')

@section('content')

    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Relatório de Itens Imcompletos</a>
        </li>
    </ol>

    <!-- DataTables Example -->
    <div class="card mb-3">
        <div class="card-header">
            <i class="fas fa-table"></i>
            Itens Imcompletos
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable1" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        {{--Geral--}}
                        <th>Status</th>
                        <th>SKU</th>
                        <th>Nome</th>
                        <th>price</th>
                        <th>Descrição</th>
                        <th>Descrição Resumida</th>
                        {{--Peso e Dimensões--}}
                        <th>Peso</th>
                        <th>Altura</th>
                        <th>Largura</th>
                        <th>Comprimento</th>
                        <th>Altura Produto s/ Cx</th>
                        <th>Largura Produto s/ Cx</th>
                        <th>Comprimento Produto s/ Cx</th>
                        {{--Atributos do Produto--}}
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Linha</th>
                        <th>Tensão</th>
                        <th>Cor</th>
                        <th>Tempo de Garantia</th>
                        <th>Caracteristicas</th>
                        {{--Preço Avançado--}}
                        <th>special_price</th>
                        {{--Mecanismos de Busca (SEO)--}}
                        <th>Url</th>
                        <th>Meta Título</th>
                        <th>Meta Palavras-Chave</th>
                        <th>Meta Descrição</th>
                        {{--Atributos Anymarket--}}
                        <th>integra_anymarket</th>
                        <th>Título Anymarket</th>
                        <th>Origem Anymarket</th>
                        <th>Markup</th>
                        <th>Calculo Preço</th>
                        <th>Código Barra</th>
                        <th>Código Ncm</th>
                        {{--Categorias do Produto--}}
                        <th>Categorias</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($dados as $item)
                        <tr>
                            {{--Geral--}}
                            <td>{{ $item['status'] or old('status') }}</td>
                            <td>{{ $item['sku'] or old('sku') }}</td>
                            <td>{{ $item['name'] or old('name') }}</td>
                            <td>{{ $item['price'] or old('price') }}</td>
                            <td> @if(!empty($item['description']))
                                    <button type="button" class="btn btn-default" data-toggle="tooltip" data-placement="left" title="{{ $item['description'] }}">Descrição</button>
                                @endif
                            </td>
                            <td> @if(!empty($item['short_description']))
                                    <button type="button" class="btn btn-default" data-toggle="tooltip" data-placement="left" title="{{ $item['short_description'] }}">Descrição Resumida</button>
                                @endif
                            </td>
                            {{--Peso e Dimensões--}}
                            <td>{{ $item['weight'] or old('weight') }}</td>
                            <td>{{ $item['volume_altura'] or old('volume_altura') }}</td>
                            <td>{{ $item['volume_largura'] or old('volume_largura') }}</td>
                            <td>{{ $item['volume_comprimento'] or old('volume_comprimento') }}</td>
                            <td>{{ $item['volume_altura_produto'] or old('volume_altura_produto') }}</td>
                            <td>{{ $item['volume_largura_produto'] or old('volume_largura_produto') }}</td>
                            <td>{{ $item['volume_comprimento_produto'] or old('volume_comprimento_produto') }}</td>
                            {{--Atributos do Produto--}}
                            <td>{{ $item['computer_manufacturers'] or old('computer_manufacturers') }}</td>
                            <td>{{ $item['modelo'] or old('modelo') }}</td>
                            <td>{{ $item['manufacturer'] or old('manufacturer') }}</td>
                            <td>{{ $item['tensao'] or old('tensao') }}</td>
                            <td>{{ $item['color'] or old('color') }}</td>
                            <td>{{ $item['tempo_de_garantia'] or old('tempo_de_garantia') }}</td>
                            <td>
                                @if(!empty($item['caracteristicas']))
                                    <button type="button" class="btn btn-default" data-toggle="tooltip" data-placement="left" title="{{ $item['caracteristicas'] }}">Caracteristicas</button>
                                @endif
                            </td>
                            {{--Preço Avançado--}}
                            <td>{{ $item['special_price'] or old('special_price') }}</td>
                            {{--Mecanismos de Busca (SEO)--}}
                            <td>{{ $item['url_key'] or old('url_key') }}</td>
                            <td>{{ $item['meta_title'] or old('meta_title') }}</td>
                            <td>{{ $item['meta_keyword'] or old('meta_keyword') }}</td>
                            <td>{{ $item['meta_description'] or old('meta_description') }}</td>
                            {{--Atributos Anymarket--}}
                            <td>{{ $item['integra_anymarket'] or old('integra_anymarket') }}</td>
                            <td>{{ $item['titulo_anymarket'] or old('titulo_anymarket') }}</td>
                            <td>{{ $item['origem_anymarket'] or old('origem_anymarket') }}</td>
                            <td>{{ $item['markup'] or old('markup') }}</td>
                            <td>{{ $item['calculo_preco'] or old('calculo_preco') }}</td>
                            <td>{{ $item['codigo_barra_produto'] or old('codigo_barra_produto') }}</td>
                            <td>{{ $item['codigo_ncm'] or old('codigo_ncm') }}</td>
                            {{--Categorias do Produto--}}
                            <td>
                                @foreach($item['categories'] as $cat)
                                    {{ $cat or old('categories') }}
                                @endforeach
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="90">Não Existem Itens</td>
                        </tr>
                    @endforelse

                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@push('scripts')

<script>


    $(document).ready(function() {
        $('#dataTable1').DataTable( {
            "lengthMenu": [[3,10, 25, 50, -1], [3,10, 25, 50, "All"]],
            "pageLength": 3,
            "columnDefs": [{
                "targets": [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32],
                "createdCell": function (td, cellData, rowData, row, col) {
                    if (cellData == ""){
                        $(td).css('background-color', '#ff9797').css('color', '#ffffff');
                    }
                }
            }]
        } );
    } );



</script>

@endpush
