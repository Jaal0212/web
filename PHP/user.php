<?php 
include 'conexion.php';
       
// Verifica si el dato está disponible en la sesión
if (!isset($_SESSION['id_usuario'])) {
    // Si no está configurada, redirige al login
    header('Location: ../index.php');
    exit(); // Asegúra de que el script se detenga después de redirigir
}else{
    // Recupera el dato
    $usuario = $_SESSION['nombre'];
}
?>
<ul class="navbar-nav ml-auto">                                                

    <div class="topbar-divider d-none d-sm-block"></div>

    <!-- Nav Item - User Information -->
    <li class="nav-item dropdown no-arrow">
        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $usuario; ?></span>
            <img class="img-profile rounded-circle"
                src="../assets/imgs/undraw_profile.svg" width="30px">
        </a>
        <!-- Dropdown - User Information -->
        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
            aria-labelledby="userDropdown">
            <a class="dropdown-item" href="perfil.php">
                <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400">Perfil</i>                
            </a>
            <a class="dropdown-item" href="perfil.php?editar">
                <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400">Configuración</i>                
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href='logout.php' data-target="#logoutModal">
                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400">Cerrar sesión</i>                                 
            </a>
        </div>
    </li>

</ul>
