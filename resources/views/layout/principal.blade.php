
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Magento Hub</title>

    <!-- Bootstrap core CSS-->
    <link rel="stylesheet" href="{{url("public/vendor/bootstrap/css/bootstrap.min.css")}}">

    <!-- Custom fonts for this template-->
    <link rel="stylesheet" href="{{url("public/vendor/fontawesome-free/css/all.min.css")}}">

    <!-- Page level plugin CSS-->
    <link rel="stylesheet" href="{{url("public/vendor/datatables/dataTables.bootstrap4.css")}}">

    <!-- Custom styles for this template-->
    <link rel="stylesheet" href="{{url("public/css/sb-admin.css")}}">

</head>

<body id="page-top">

<nav class="navbar navbar-expand navbar-dark bg-dark static-top">

    <a class="navbar-brand mr-1" href="{{ url("/") }}">Magento Hub</a>

    <button class="btn btn-link btn-sm text-white order-1 order-sm-0" id="sidebarToggle" href="#">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Navbar Search -->
    <form class="d-none d-md-inline-block form-inline ml-auto mr-0 mr-md-3 my-2 my-md-0">
        {{--<div class="input-group">
            <input type="text" class="form-control" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
            <div class="input-group-append">
                <button class="btn btn-primary" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>--}}
    </form>

    <!-- Navbar -->
    <ul class="navbar-nav ml-auto ml-md-0">

        @if (Auth::guest())
            <li class="nav-item text-nowrap">
                <a class="nav-link" href="{{ url('/login') }}">Login</a>
            </li>
            <li class="nav-item text-nowrap">
                <a class="nav-link" href="{{ url('/register') }}">Register</a>
            </li>
        @else
            @if (Auth::user()->type == 'admin')
                <li class="nav-item dropdown no-arrow mx-1">
                    <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-bell fa-fw"></i>
                        <span class="badge badge-danger">9+</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="alertsDropdown">
                        <a class="dropdown-item" href="#">Action</a>
                        <a class="dropdown-item" href="#">Another action</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">Something else here</a>
                    </div>
                </li>
                <li class="nav-item dropdown no-arrow mx-1">
                    <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-envelope fa-fw"></i>
                        <span class="badge badge-danger">7</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="messagesDropdown">
                        <a class="dropdown-item" href="#">Action</a>
                        <a class="dropdown-item" href="#">Another action</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">Something else here</a>
                    </div>
                </li>
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-user-circle fa-fw"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="{{ url("configuracoes") }}">Configurações</a>
                        <a class="dropdown-item" href="{{ url("logs") }}">Logs</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ url('logout') }}">Logout</a>
                        {{--<a class="dropdown-item" href="{{ url('/logout') }}" data-toggle="modal" data-target="#logoutModal">Logout</a>--}}
                    </div>
                </li>
            @else
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-user-circle fa-fw"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="{{ url('logout') }}">Logout</a>
                        {{--<a class="dropdown-item" href="{{ url('/logout') }}" data-toggle="modal" data-target="#logoutModal">Logout</a>--}}
                    </div>
                </li>
            @endif
        @endif

    </ul>

</nav>

<div id="wrapper">

    <!-- Sidebar -->
    <ul class="sidebar navbar-nav">
        @if (Auth::user()->type == 'admin')
            <li class="nav-item active">
                <a class="nav-link" href="{{ url("/") }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ url("/itensintegra") }}" id="integraItem">
                    <i class="fas fa-layer-group"></i>
                    <span>Integração Itens</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ url("/itensintegrasaldo") }}" id="integraSaldo">
                    <i class="fas fa-truck-loading"></i>
                    <span>Integração Estoque</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ url("/pedidosintegra") }}">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Integração Pedidos</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ url("/formComunicaFaturamento") }}" id="ComunFatur">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Comunica faturamento</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ url("/formEnviaProdFunc") }}">
                    <i class="fas fa-shopping-basket"></i>
                    <span>Envio Produtos de Func.</span></a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-fw fa-folder"></i>
                    <span>Relatórios</span>
                </a>
                <div class="dropdown-menu" aria-labelledby="pagesDropdown">
                    <h6 class="dropdown-header">Relatórios Magento:</h6>
                    <a class="dropdown-item" href="{{ url('/relpedidos') }}">Relatório de Vendas</a>
                    <a class="dropdown-item" href="{{ url('/relitensincompletos') }}">Itens Incompletos</a>
                    <a class="dropdown-item" href="{{ url('/relitenssemimg') }}">Itens S/ Imagem</a>
                    <a class="dropdown-item" href="{{ url('/processos') }}">Processos</a>
                </div>
            </li>
        @elseif(Auth::user()->type == 'logistica')
            <li class="nav-item active">
                <a class="nav-link" href="{{ url("/") }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ url("/formComunicaFaturamento") }}" id="ComunFatur">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Comunica faturamento</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ url("/formEnviaProdFunc") }}">
                    <i class="fas fa-shopping-basket"></i>
                    <span>Envio Produtos de Func.</span></a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-fw fa-folder"></i>
                    <span>Relatórios</span>
                </a>
                <div class="dropdown-menu" aria-labelledby="pagesDropdown">
                    <h6 class="dropdown-header">Relatórios Magento:</h6>
                    <a class="dropdown-item" href="{{ url('/relpedidos') }}">Relatório de Vendas</a>
                </div>
            </li>
        @endif
    </ul>

    <div id="content-wrapper">

        <div class="container-fluid">

            @yield('content')

        </div>
        <!-- /.container-fluid -->

        <!-- Sticky Footer -->
        <footer class="sticky-footer">
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <span>Copyright © Your Website 2018</span>
                </div>
            </div>
        </footer>

    </div>
    <!-- /.content-wrapper -->

</div>
<!-- /#wrapper -->

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Logout Modal-->
{{--
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <a class="btn btn-primary" href="{{ url('/logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    Logout
                </a>
                <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                </form>
            </div>
        </div>
    </div>
</div>
--}}
<!-- Bootstrap core JavaScript-->
<script src="{{url("public/vendor/jquery/jquery.min.js")}}"></script>
<script src="{{url("public/vendor/bootstrap/js/bootstrap.bundle.min.js")}}"></script>

<!-- Core plugin JavaScript-->
<script src="{{url("public/vendor/jquery-easing/jquery.easing.min.js")}}"></script>

<!-- Page level plugin JavaScript-->
{{--<script src="{{url("public/vendor/chart.js/Chart.min.js")}}"></script>--}}
<script src="{{url("public/vendor/datatables/jquery.dataTables.js")}}"></script>
<script src="{{url("public/vendor/datatables/dataTables.bootstrap4.js")}}"></script>

<!-- Custom scripts for all pages-->
<script src="{{url("public/js/sb-admin.js")}}"></script>
@stack('scripts')

<!-- Demo scripts for this page-->
<script src="{{url("public/js/demo/datatables-demo.js")}}"></script>
{{--<script src="{{url("public/js/demo/chart-area-demo.js")}}"></script>--}}

</body>

</html>
