<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Carrito</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Stylesheet -->
    <link href="../assets/css/Style.css" rel="stylesheet">
    <link href="../assets/css/StyleCarrito.css" rel="stylesheet">

</head>

<body>

    <!-- Navbar -->
    <?php
    // Inicia la sesión
    session_start();
    require_once "menu.php";
    /*
    Realiza una consulta para ver si el carrito no a pasado a pedido, 
    si es asi no cargar nada, si no cargar los productos que hay en detalle_carrito
    */
    $sql_check = "SELECT c.id_carrito
                    FROM carrito c
                    JOIN usuarios u ON c.id_usuario = u.id_usuario
                    WHERE NOT EXISTS (
                    SELECT 1
                    FROM pedidos
                    WHERE pedidos.id_carrito = c.id_carrito);";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute();
    //si no hay productos en carrito, redirigir a la pagina productos
    if (!$stmt_check->rowCount() > 0) {
        echo "<script>
            alert('Aun no hay productos en carrito');
            window.location.href = 'Productos.php';
          </script>";
        exit();
    }
    $carrito = $stmt_check->fetch();
    $id_carrito= $carrito['id_carrito'];
    $id_usuario=$_SESSION['id_usuario'];
    $consultaCart = "SELECT dc.*, p.nombre, p.imagen
                        FROM detalle_carrito dc
                        JOIN productos p ON dc.id_producto = p.id_productos
                        JOIN carrito c ON dc.id_carrito = c.id_carrito
                        WHERE dc.id_carrito = :id_carrito
                        AND c.id_usuario = :id_usuario;
                    ";    
    // Ejecutar la consulta para obtener los productos        
    $resultado = $conn->prepare($consultaCart);
    $resultado->bindParam(':id_carrito', $id_carrito);
    $resultado->bindParam(':id_usuario', $id_usuario);
    $resultado->execute();
    $precioTotal=0;
    ?>

    <!-- Cart Start -->
    <div class="container-fluid">
        <div class="row px-xl-5">
            <div class="col-lg-8 table-responsive mb-5">
                <table class="table table-light table-borderless table-hover text-center mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Productos</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Tamaño</th>
                            <th>Extras</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle">
                        <?php
                        // Mostrar los resultados
                        if ($resultado->rowCount() > 0) {
                            while($item_cart = $resultado->fetch()){
                                echo "
                                    <tr>
                                        <td class='align-middle'><img src='".$item_cart['imagen']."' alt=''
                                                style='width: 50px;'> ".$item_cart['nombre']."</td>
                                        <td class='align-middle' id='preciounidad'>".$item_cart['precio']."</td>
                                        <td class='align-middle' id='cantidad'>".$item_cart['cantidad']."</td>
                                        <td class='align-middle' id='tamanio'>".$item_cart['tamanio']."</td>
                                        <td class='align-middle' id='extra'>".$item_cart['descripcion_extras']."</td>                                        
                                        <td class='align-middle'>
                                            <form method='POST'>
                                                <input type='hidden' name='id_detalle_carrito' value='".$item_cart['id_detalle_carrito']."'>
                                                <button class='boton btn-sm btn-danger' type='submit' name='eliminar'>
                                                    <i class='fa fa-times'></i>
                                                </button>
                                            </form>
                                    </tr>
                                ";
                                $precioTotal+=$item_cart['precio'];
                            }
                        }
                        if (isset($_POST['eliminar'])) {
                            $id_detalle_carrito = $_POST['id_detalle_carrito'];
                            
                            // Preparar y ejecutar la consulta para eliminar el producto del carrito
                            $consulta = "DELETE FROM detalle_carrito WHERE id_detalle_carrito = :id_detalle_carrito";
                            $stmt = $conn->prepare($consulta);
                            $stmt->bindParam(':id_detalle_carrito', $id_detalle_carrito, PDO::PARAM_INT);
                        
                            if ($stmt->execute()) {
                                echo "<script>
                                        alert('Producto eliminado correctamente');
                                        window.location.href = 'cart.php';
                                    </script>";  // Recargar la página para reflejar el cambio
                                exit();
                            } else {
                                echo "<script>alert('Error al eliminar el producto');</script>";
                            }
                        }                        
                        ?>                        
                    </tbody>
                </table>
            </div>
            <div class="col-lg-4">
                <div class="bg-light p-30 mb-5">
                    <h5 class="section-title position-relative text-uppercase mb-3">Total</h5>
                    <div class="pt-2">
                        <h5 class="Total" id="total-price">$<?php echo $precioTotal;?></h5>
                        <form method="POST">
                            <button type="submit" class="boton btn-block btn-primary font-weight-bold my-3 py-3" name="realizar_pedido">
                                Realizar pedido
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Cart End -->
    <?php
    // Verifica si se ha hecho clic en el botón de "Realizar pedido"
    if (isset($_POST['realizar_pedido'])) {                
        $id_usuario = $_SESSION['id_usuario'];  // Toma el ID del usuario que está almacenado en la sesión
        // Obtener la fecha y hora actual en formato Y-m-d H:i:s (Año-Mes-Día Hora:Minuto:Segundo)
        $fecha_actual = date('Y-m-d H:i:s');
        // Insertar el pedido en la tabla 'pedido'
        $consulta_pedido = "INSERT INTO pedidos (id_usuario, id_carrito, estado_pedido, fecha_pedido) 
                            VALUES (:id_usuario, :id_carrito, 'En proceso', :fecha)";
        $stmt_pedido = $conn->prepare($consulta_pedido);
        $stmt_pedido->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt_pedido->bindParam(':id_carrito', $id_carrito, PDO::PARAM_INT);
        $stmt_pedido->bindParam(':fecha', $fecha_actual, PDO::PARAM_INT);
    
        if ($stmt_pedido->execute()) {
               
            echo "<script>
                    alert('Pedido realizado con éxito');
                    window.location.href = 'pedidos.php';  // Redirige a pedido
                  </script>";
        } else {
            echo "<script>alert('Error al realizar el pedido.');</script>";
        }
    }
    ?>
    <!-- incrementar o decrementar cantidad 
    <script src="../assets/js/btn_plus_min.js"></script>-->
    <!-- core  -->
    <script src="../assets/vendors/jquery/jquery-3.4.1.js"></script>
    <script src="../assets/vendors/bootstrap/bootstrap.bundle.js"></script>

    <!-- bootstrap affix -->
    <script src="../assets/vendors/bootstrap/bootstrap.affix.js"></script>

</body>

</html>