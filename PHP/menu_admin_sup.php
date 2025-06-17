<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Dashboard</title>

    <!-- Custom fonts-->
    <link href="../assets/vendors/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles-->
    <link href="../assets/css/sb-admin-2.min.css" rel="stylesheet">

</head>

<body id="page-top">
    <?php
    // Verifica si la variable de sesión 'id_usuario' está configurada
    if (!isset($_SESSION['id_usuario'])) {
        // Si no está configurada, redirige al login
        header('Location: ../index.php');
        exit(); // Asegúra de que el script se detenga después de redirigir
    }
    ?>

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="Principal.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-laugh-wink"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Administrador</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item active">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Contenido
            </div>

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages"
                    aria-expanded="true" aria-controls="collapsePages">
                    <i class="fas fa-fw fa-folder"></i>
                    <span>Tablas</span>
                </a>
                <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Usuarios:</h6>
                        <a class="collapse-item" href="tables.php?usuarios=editar">Editar</a>
                        <a class="collapse-item" href="tables.php?usuarios=añadir">Añadir</a>
                        <a class="collapse-item" href="tables.php?usuarios=eliminar">Eliminar</a>
                        <a class="collapse-item" href="tables.php?usuarios=buscar">Buscar</a>
                        <div class="collapse-divider"></div>
                    </div>
                </div>
                <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Productos:</h6>
                        <a class="collapse-item" href="tables.php?productos=editar">Editar</a>
                        <a class="collapse-item" href="tables.php?productos=añadir">Añadir</a>
                        <a class="collapse-item" href="tables.php?productos=eliminar">Eliminar</a>
                        <a class="collapse-item" href="tables.php?productos=buscar">Buscar</a>
                        <div class="collapse-divider"></div>
                    </div>
                </div>
                <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Categorias:</h6>
                        <a class="collapse-item" href="tables.php?categorias=editar">Editar</a>
                        <a class="collapse-item" href="tables.php?categorias=añadir">Añadir</a>
                        <a class="collapse-item" href="tables.php?categorias=eliminar">Eliminar</a>
                        <a class="collapse-item" href="tables.php?categorias=buscar">Buscar</a>
                        <div class="collapse-divider"></div>
                    </div>
                </div>
                <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Pedidos:</h6>
                        <a class="collapse-item" href="tables.php?pedidos=editar">Editar</a>
                        <a class="collapse-item" href="tables.php?pedidos=eliminar">Eliminar</a>
                        <a class="collapse-item" href="tables.php?pedidos=buscar">Buscar</a>
                        <div class="collapse-divider"></div>
                    </div>
                </div>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <a href="Principal.php"><button class="rounded-circle border-0" id="sidebarToggle"></button></a>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <?php require_once "user.php"?>                      
                </nav>
                <!-- End of Topbar -->