<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="Cooffe Shop" content="Devcrud">
    <title>Cooffe Shop</title>

    <!-- font icons -->
    <link rel="stylesheet" href="../assets/vendors/themify-icons/css/themify-icons.css">
    <link rel="stylesheet" href="../assets/vendors/animate/animate.css">

    <!-- main styles -->
    <link rel="stylesheet" type="text/css" href="../assets/css/Style.css">

</head>

<body data-spy="scroll" data-target=".navbar" data-offset="40" id="home">
    <!-- menu -->
    <?php
    // Inicia la sesión
    session_start();
    // Verifica si la variable de sesión 'id_usuario' está configurada
    if (!isset($_SESSION['id_usuario'])) {
        // Si no está configurada, redirige al login
        header('Location: ../index.php');
        exit(); // Asegúra de que el script se detenga después de redirigir
    }
    require_once "menu.php";
    // Ejecutar la consulta para obtener los productos
    $consulta = "SELECT * FROM productos LIMIT 12";
    $resultado = $conn->prepare($consulta);
    $resultado->execute();
    
    ?>
    <!-- header -->
    <header id="home" class="header">
        <div class="overlay text-white text-center">
            <h1 class="display-2 font-weight-bold my-3">Coffee Shop</h1>
            <!-- eslogan pendiente <h2 class="display-4 mb-5">Siempre fresco y delicioso</h2>  -->
            <a class="btn btn-lg btn-primary" style="Color: white" href="#gallary">Productos</a>
        </div>
    </header>

    <!--  gallary Section  -->
    <div id="gallary" class="text-center bg-dark text-light has-height-md middle-items wow fadeIn">
        <h2 class="section-title">MENU</h2>
    </div>
    <div class="gallary row">
        <?php
        if ($resultado->rowCount() > 0) {
            while ($producto = $resultado->fetch()) {
                
                echo "
                    <div class='col-sm-6 col-lg-3 gallary-item wow fadeIn''>
                        <img src='".$producto['imagen']."' class='gallary-img'>
                        <a href='Detalle.php?producto=" . $producto['id_productos'] . "' class='gallary-overlay'>
                            <i class='gallary-icon ti-plus'></i>
                        </a>
                    </div>
                ";
            }
        }
        ?>
        
    </div>
    
    <!-- core  -->
    <script src="../assets/vendors/jquery/jquery-3.4.1.js"></script>
    <script src="../assets/vendors/bootstrap/bootstrap.bundle.js"></script>

    <!-- bootstrap affix -->
    <script src="../assets/vendors/bootstrap/bootstrap.affix.js"></script>

    <!-- logout automatic-->
    <script src="../assets/js/logout.js"></script>

</body>

</html>