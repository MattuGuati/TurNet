<!DOCTYPE html>
<html lang="es" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="/cursoudemy/assets/" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>DIGITUR</title>
    <meta name="description" content="" />
    <link rel="icon" type="image/x-icon" href="/cursoudemy/assets/img/favicon/favicon.ico" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/cursoudemy/assets/vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="/cursoudemy/assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="/cursoudemy/assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="/cursoudemy/assets/css/demo.css" />
    <link rel="stylesheet" href="/cursoudemy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="/cursoudemy/assets/css/sweetalert2.min.css" />
    <link rel="stylesheet" href="/cursoudemy/assets/vendor/css/pages/page-auth.css" />
    <link rel="stylesheet" href="/cursoudemy/assets/vendor/libs/apex-charts/apex-charts.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="index.html" class="app-brand-link">
                        <span class="app-brand-logo demo">
                            <img src="/cursoudemy/assets/img/logo.png" alt="Logo" width="50">
                        </span>
                        <span class="app-brand-text demo menu-text fw-bolder ms-2">DIGITUR</span>
                    </a>
                    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
                        <i class="bx bx-chevron-left bx-sm align-middle"></i>
                    </a>
                </div>
                <div class="menu-inner-shadow"></div>
                <ul class="menu-inner py-1">
                    <li class="menu-item">
                        <a href="/cursoudemy/views/inicio" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-home-circle"></i>
                            <div data-i18n="Inicio">Inicio</div>
                        </a>
                    </li>
                    <li class="menu-header small text-uppercase">
                        <span class="menu-header-text">ADMINISTRACIÓN</span>
                    </li>
                    <li class="menu-item">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons bx bx-user"></i>
                            <div data-i18n="Clientes">Clientes</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item">
                                <a href="/cursoudemy/views/clientes" class="menu-link">
                                    <div data-i18n="Listado Clientes">Listado Clientes</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="menu-item">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons bx bx-cube-alt"></i>
                            <div data-i18n="Servicios">Servicios</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item">
                                <a href="/cursoudemy/views/servicios" class="menu-link">
                                    <div data-i18n="Listado Servicios">Listado Servicios</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="menu-item">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons bx bx-cube-alt"></i>
                            <div data-i18n="Modulos">Modulos</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item">
                                <a href="/cursoudemy/views/modulos" class="menu-link">
                                    <div data-i18n="Listado Modulos">Listado Modulos</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="menu-header small text-uppercase">
                        <span class="menu-header-text">GESTION TURNOS</span>
                    </li>
                    <li class="menu-item active open">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons bx bx-dock-top"></i>
                            <div data-i18n="Account Settings">Atender Turnos</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item" id="generarturno">
                                <a href="/cursoudemy/views/generarturno" class="menu-link">
                                    <div data-i18n="Account">Generar Turno</div>
                                </a>
                            </li>
                            <li class="menu-item active">
                                <a href="/cursoudemy/views/gestionarturno" class="menu-link">
                                    <div data-i18n="Notifications">Atender Turnos</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="menu-header small text-uppercase"><span class="menu-header-text">REPORTES</span></li>
                    <li class="menu-item">
                        <a href="/cursoudemy/views/reportes" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-file"></i>
                            <div data-i18n="Reportes">Reportes</div>
                        </a>
                    </li>
                </ul>
            </aside>
            <div class="layout-page">
                <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                            <i class="bx bx-menu bx-sm"></i>
                        </a>
                    </div>
                    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                        <ul class="navbar-nav flex-row align-items-center ms-auto">
                            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                                    <div class="avatar avatar-online">
                                        <img src="/cursoudemy/assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="avatar avatar-online">
                                                        <img src="/cursoudemy/assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <span class="fw-semibold d-block">John Doe</span>
                                                    <small class="text-muted">Admin</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="/cursoudemy/views/logout.php">
                                            <i class="bx bx-power-off me-2"></i>
                                            <span class="align-middle">Cerrar Sesión</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </nav>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="row">
                            <div class="col-lg-12 mb-4 order-0">
                                <div class="card">
                                    <div class="d-flex align-items-end row">
                                        <div class="col-sm-7">
                                            <div class="card-body">
                                                <h5 class="card-title text-primary">Datos del Usuario</h5>
                                                <p class="mb-4">
                                                    Servicio: <span id="nombreservicio"></span><br>
                                                    Modulo: <span id="nombremodulo"></span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-sm-5 text-center text-sm-left">
                                            <div class="card-body pb-0 px-0 px-md-4">
                                                <img src="/cursoudemy/assets/img/illustrations/man-with-laptop-light.png" height="140" alt="View Badge User" data-app-dark-img="illustrations/man-with-laptop-dark.png" data-app-light-img="illustrations/man-with-laptop-light.png" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 mb-4 order-0">
                                <div class="card">
                                    <div class="row row-bordered g-0">
                                        <div class="col-md-12">
                                            <h5 class="card-header m-0 me-2 pb-3">Gestionar Turno</h5>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <button type="button" class="btn btn-primary" id="llamarturno" onclick="llamar_Turno()">
                                                            <span class="tf-icons bx bx-play"></span>&nbsp; LLAMAR
                                                        </button>
                                                        <button type="button" class="btn btn-primary" id="atenderturno" onclick="atender_Turno()">
                                                            <span class="tf-icons bx bx-play"></span>&nbsp; ATENDER
                                                        </button>
                                                        <button type="button" class="btn btn-danger" id="finalizarturno" onclick="finalizar_Turno()">
                                                            <span class="tf-icons bx bx-stop"></span>&nbsp; FINALIZAR
                                                        </button>
                                                    </div>
                                                    <div class="col-md-8" id="vista_numero">
                                                        <div class="card">
                                                            <div class="card-body">
                                                                <h5 class="card-title">Turno: <span id="numerodelturno"></span></h5>
                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">DOCUMENTO</label>
                                                                            <input type="text" class="form-control" id="documento" readonly />
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">NUMERO</label>
                                                                            <input type="text" class="form-control" id="numero" readonly />
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">PRIMER NOMBRE</label>
                                                                            <input type="text" class="form-control" id="pnombre" readonly />
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">PRIMER APELLIDO</label>
                                                                            <input type="text" class="form-control" id="papellido" readonly />
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">SEGUNDO APELLIDO</label>
                                                                            <input type="text" class="form-control" id="sapellido" readonly />
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 mb-4 order-0">
                                <div class="card">
                                    <div class="row row-bordered g-0">
                                        <div class="col-md-4">
                                            <div class="card-body">
                                                <div class="card-title">
                                                    <h5>ACTIVOS: <span id="en_espera"></span></h5>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card-body">
                                                <div class="card-title">
                                                    <h5>ATENDIDOS: <span id="turnos_atendidos"></span></h5>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card-body">
                                                <div class="card-title">
                                                    <h5>TOTALES: <span id="total_turnos"></span></h5>
                                                </div>
                                                <button type="button" class="btn btn-primary" onclick="modalverturnos()">
                                                    Ver turnos
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <footer class="content-footer footer bg-footer-theme">
                        <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                            <div class="mb-2 mb-md-0">
                                © 2025, made with ❤️ by
                                <a href="https://themeselection.com" target="_blank" class="footer-link fw-bolder">ThemeSelection</a>
                            </div>
                            <div>
                                <a href="https://themeselection.com/license/" class="footer-link me-4" target="_blank">License</a>
                                <a href="https://themeselection.com/" target="_blank" class="footer-link me-4">More Themes</a>
                                <a href="https://themeselection.com/demo/sneat-bootstrap-html-admin-template/documentation/" target="_blank" class="footer-link me-4">Documentation</a>
                                <a href="https://github.com/themeselection/sneat-html-admin-template-free/issues" target="_blank" class="footer-link me-4">Support</a>
                            </div>
                        </div>
                    </footer>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modalturnos" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel4">Listado de Turnos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <table id="TablaTurnos" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Estado</th>
                                            <th>Turno</th>
                                            <th>Servicio</th>
                                            <th>Numero</th>
                                            <th>Nombre</th>
                                            <th>Tiempo Ingreso</th>
                                            <th>Tiempo Salida</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <script src="/cursoudemy/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="/cursoudemy/assets/vendor/libs/popper/popper.js"></script>
    <script src="/cursoudemy/assets/vendor/js/bootstrap.js"></script>
    <script src="/cursoudemy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="/cursoudemy/assets/js/sweetalert2.all.min.js"></script>
    <script src="/cursoudemy/assets/vendor/js/menu.js"></script>
    <script src="/cursoudemy/assets/vendor/libs/apex-charts/apexcharts.js"></script>
    <script src="/cursoudemy/assets/js/main.js"></script>
    <script src="/cursoudemy/assets/js/dashboards-analytics.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="/cursoudemy/controllers/gestionarturnoController.js"></script>
</body>
</html>