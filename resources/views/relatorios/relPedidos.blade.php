@extends('layout.principal')

@section('content')

    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Relatório de Pedidos</a>
        </li>
    </ol>

    <div class="card">
        <div class="card-header">
            Filtros
        </div>
        <div class="card-body">

            <form>
                <div class="form-group row">
                    <label for="inputDataIni" class="col-sm-2 col-form-label">Data Inicial:</label>
                    <div class="col-sm-4">
                        <input type="date" class="form-control" id="inputDataIni" placeholder="Data Inicial">
                    </div>
                    <label for="inputDataFim" class="col-sm-2 col-form-label">Data Final:</label>
                    <div class="col-sm-4">
                        <input type="date" class="form-control" id="inputDataFim" placeholder="Data Final">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="selectStatus" class="col-sm-2 col-form-label"t>Status dos Pedidos:</label>
                    <div class="col-sm-4">
                        <select class="form-control" id="selectStatus" >
                            <option>Selecione...</option>
                            <option name="status" value="processing">Pago</option>
                            <option name="status" value="complete_nf">Faturado</option>
                            <option name="status" value="complete">Despachado</option>
                            <option name="status" value="entregue">Entregue</option>
                            <option name="status" value="canceled">Cancelado</option>
                            <option name="status" value="pendente">Pendente</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-2">
                        <button type="button" class="btn btn-primary status" id="filtroStatus">Buscar</button>
                    </div>
                </div>
            </form>

            <form>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <div class="table-responsive" style="width: 100%">
                <table id="statusPedidos" class="table table-striped table-bordered nowrap">
                    <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Cliente</th>
                        <th>Pedido MkPlace</th>
                        <th>Data do Pedido</th>
                        <th>SubTotal</th>
                        <th>Frete</th>
                        <th>Desconto</th>
                        <th>Total Pago</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th>Pedido</th>
                        <th>Cliente</th>
                        <th>Pedido MkPlace</th>
                        <th>Data do Pedido</th>
                        <th>SubTotal</th>
                        <th>Frete</th>
                        <th>Desconto</th>
                        <th>Total Pago</th>
                    </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>



@endsection

@push('scripts')

<script>

    $(document).ready(function() {

        //Tratamento para inclusão de data no campo dataIni
        var dataAtual = new Date();
        //console.log(dataAtual);
        var diasCalc = 15
        var previsao = new Date(dataAtual.getTime() - (diasCalc * 24 * 60 * 60 * 1000));

        var mes = previsao.getMonth() + 1;
        if (mes < 10) {
            mes  = "0" + mes;
        }
        var day = previsao.getDate();
        if (day< 10) {
            day  = "0" + day;
        }
        var year = previsao.getFullYear();

        var dataIni = year + "-" + mes + "-" + day;
        $('#inputDataIni').val(dataIni);

        //Inclisão da data no campo de dataFim
        var dataFim = new Date().toISOString().split('T')[0];
        $('#inputDataFim').val(dataFim);
        //$('#inputDataFim').prop("disabled",true);

        $('#filtroStatus').on('click', function () {
            var baseUrl = window.location.protocol + "//" + window.location.host + "/" + window.location.pathname.split('/')[1];
            var status = $("select option:selected").val();
            var dataIni = $("#inputDataIni").val();
            var dataFim = $("#inputDataFim").val();
            //console.log(status);
            //console.log(dataIni);
            //console.log(dataFim);

            var url = baseUrl+'/relpedidosjson/' + status + '/' + dataIni + '/' + dataFim;

            $('#statusPedidos').DataTable({
                destroy: true,
                'language':{
                    "loadingRecords": "&nbsp;",
                    "processing": "Carregando...",
                    "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Portuguese-Brasil.json"
                },
                ajax: {
                    url: url ,
                    dataSrc: ''
                },
                columns: [
                    {
                        render: function (data, type, row, meta) {
                            //console.log(row.increment_id);
                            return '<a href="#" onclick=""><i class="fa fa-search fa-lg"></i>' + row.increment_id + '</a>';
                        }
                    },
                    {
                        render: function (data, type, row, meta) {
                            return row.customer_firstname + " " + row.customer_lastname
                        }

                    },
                    {
                        render: function (data,type,row, meta) {
                            if (row.payment.method == "checkmo"){
                                console.log(row.payment.method);
                                for (var i = 0; i < row.status_history.length; i++){

                                    if (row.status_history[i].status == "pending" && row.status_history[i].is_customer_notified	 == 0){
                                        var comment = row.status_history[i].comment.split("<br>");
                                        console.log(comment);
                                        //console.log(comment[0].substring(44,57));
                                        return comment[0].substring(44,57);
                                    }
                                }
                            }
                        },
                        defaultContent: ""
                    },
                    {
                        data: "created_at",
                        render: function ( data, type, row ) {
                            var textSplit = data.split(' ');
                            var dateSplit = textSplit[0].split('-');
//                            console.log(dateSplit);
                            return type === "display" || type === "filter" ?
                                dateSplit[2] +'-'+ dateSplit[1] +'-'+ dateSplit[0] : data;
                        }
                    },
                    {
                        data: "subtotal",
                        render: $.fn.dataTable.render.number( ',', '.', 2, 'R$ ' )
                    },
                    {
                        data: "shipping_amount",
                        render: $.fn.dataTable.render.number( ',', '.', 2, 'R$ ' )
                    },
                    {
                        data: "discount_amount", //Pode ser null ou undefined
                        defaultContent: "",
                        render: $.fn.dataTable.render.number( ',', '.', 2, 'R$ ' )
                    },
                    {
                        data: "total_paid",
                        render: $.fn.dataTable.render.number( ',', '.', 2, 'R$ ' )
                    },
                ],
                columnDefs: [
                {
                    "targets": 0, // your case first column
                    "className": "text-center",
                    "width": "10%"
                }
            ]
            });


        })

    } );



</script>

@endpush
