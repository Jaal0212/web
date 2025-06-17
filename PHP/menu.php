<?php
    include 'conexion.php';
    //consulta carrito
    $sql_check = "SELECT dc.id_detalle_carrito
                    FROM carrito c
                    JOIN detalle_carrito dc ON c.id_carrito = dc.id_carrito
					JOIN usuarios u ON c.id_usuario = u.id_usuario
                    WHERE NOT EXISTS (
                    SELECT 1
                    FROM pedidos
                    WHERE pedidos.id_carrito = c.id_carrito);";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute();
    $contador=$stmt_check->rowCount();//devuelve el numero de filas en base de datos    
?>
<!-- Navbar -->
<nav class="custom-navbar navbar navbar-expand-lg navbar-dark fixed-top" data-spy="affix" data-offset-top="10">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="Principal.php">Inicio</a>
                </li>                
                <li class="nav-item">
                    <a class="nav-link" href="Productos.php">Productos</a>
                </li>
                <!--Acceso solo para administradores -->
                <?php 
                    if($_SESSION['rol']=='Administrador'){
                        echo '<li class="nav-item">
                                    <a class="nav-link" href="dashboard.php">Tablero</a>
                                </li>';
                    }
                ?>                
                <li class="nav-item">
                    <a class="nav-link" href="pedidos.php">Pedidos</a>
                </li>              
            </ul>
            <a class="navbar-brand m-auto" href="Principal.php">
                <img src="../assets/imgs/Logo.png" class="brand-img" alt="">
                <span class="brand-txt">COFFEE SHOP</span>
            </a>
            <ul class="navbar-nav">                
                <!-- Carrito -->
                <li>
                    <a href="cart.php" class="btn px-0 ml-3">
                        <img src="../assets/imgs/car.png" width="30" />
                        <span class="badge text-white border border-white rounded-circle" style="padding-bottom: 2px;"><?php echo $contador;?></span>
                    </a>
                </li>
                <!-- Usuario -->
                <li>
                    <?php 
                    require_once "user.php";                    
                    ?>  
                </li>
            </ul>                        
        </div>
    </nav>