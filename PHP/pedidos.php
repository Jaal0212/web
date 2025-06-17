<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-
    QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- main styles -->
    <link rel="stylesheet" type="text/css" href="../assets/css/Style.css">
</head>

<body>
    <!-- menu -->
    <?php
    // Inicia la sesión
    session_start();
    $rol = $_SESSION['rol'];
    $id_usuario = $_SESSION['id_usuario'];
    require_once "menu.php";
    $pedido_temp=0;
    $bandera=false;
    ?>

    <div class="container" style="translate: 0% 150px;">
        <div class="row">
            <table class="table table-light table-borderless table-hover text-center mb-0">
                <thead class="thead-dark">
                    <tr>
                        <?php
                        if ($rol != 'Común') {
                            echo "                                
                                <th>Usuario</th>
                                <th># Pedido</th>                                
                            ";
                        }
                        ?>
                        <th>Imagen</th>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Tamaño</th>
                        <th>Extras</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <?php if ($rol != 'Común')
                            echo "<th>Opciones</th>"; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    require "conexion.php";
                    // Ejecutar la consulta
                    $id_usuario = $_SESSION['id_usuario'];
                    if ($rol == 'Común') {
                        $consulta = "SELECT dc.*, p.nombre, p.imagen, ped.estado_pedido, ped.fecha_pedido
                                FROM detalle_carrito dc
                                JOIN productos p ON dc.id_producto = p.id_productos
                                JOIN carrito c ON dc.id_carrito = c.id_carrito
                                JOIN pedidos ped ON c.id_carrito = ped.id_carrito
                                WHERE ped.estado_pedido <> 'Entregado'
                                AND c.id_usuario = :id_usuario;
                                ";
                        $stmt_pedido = $conn->prepare($consulta);
                        $stmt_pedido->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
                    } else {
                        $consulta = "SELECT dc.*, p.nombre, p.imagen, ped.id_pedido ,ped.estado_pedido, ped.fecha_pedido, 
                                    CONCAT(u.nombre, ' ', u.primerap, ' ', u.segundoap) AS nombre_completo 
                                    FROM detalle_carrito dc
                                    JOIN productos p ON dc.id_producto = p.id_productos
                                    JOIN carrito c ON dc.id_carrito = c.id_carrito
                                    JOIN pedidos ped ON c.id_carrito = ped.id_carrito
									JOIN usuarios u ON c.id_usuario = u.id_usuario
                                    WHERE ped.estado_pedido <> 'Entregado';
                                ";
                        $stmt_pedido = $conn->prepare($consulta);
                    }

                    $stmt_pedido->execute();
                    $pedido = $stmt_pedido->fetchall();
                    foreach ($pedido as $key => $value) {
                        ?>
                        <tr>
                            <?php
                            if ($rol != 'Común') {
                                echo "
                                    <td>" . $value['nombre_completo'] . "</td>
                                    <td>" . $value['id_pedido'] . "</td>
                                ";
                                if($pedido_temp != $value['id_pedido']){
                                    $pedido_temp = $value['id_pedido'];
                                    $bandera=false;                                     
                                }else{
                                    $bandera= true;
                                } 
                            }
                            ?>
                            <td><?php echo "<img src='" . $value['imagen'] . "' alt=''
                                                style='width: 50px;'> "; ?></td>
                            <td><?= $value['nombre']; ?></td>
                            <td>$ <?= $value['precio']; ?></td>
                            <td><?= $value['cantidad']; ?></td>
                            <td><?= $value['tamanio']; ?></td>
                            <td><?= $value['descripcion_extras']; ?></td>
                            <td><?= date('Y-m-d H:i', strtotime($value['fecha_pedido'])); ?></td>
                            <td><?= $value['estado_pedido']; ?></td>
                            <?php
                            if ($rol != 'Común') {
                                if($bandera != true){
                                    echo "<td>                                                                                   
                                           <form action='' method='get'>
                                                <select name='opcion'>
                                                    <option value='En proceso' selected>En proceso</option>
                                                    <option value='Listo'>Listo</option>
                                                    <option value='Entregado'>Entregado</option>
                                                    <option value='Cliente no llego'>Cliente no llego</option>
                                                </select>
                                                <input type='hidden' name='editar' value='" . $value['id_detalle_carrito'] . "' />
                                                <input type='hidden' name='id_pedido' value='" . $value['id_pedido'] . "' />
                                                <button class='btn btn-info' type='submit'>Actualizar</button>
                                            </form>                                        
                                    </td>";
                                }                                
                            }
                            ?>

                        </tr>
                    <?php }//ciclo foreach
                    if (isset($_GET['editar'])) {
                        $id_detalle_carrito = $_GET['editar'];
                        $opcion_seleccionada = $_GET['opcion'];
                        $id_pedido = $_GET['id_pedido'];                        

                        // Actualización en la base de datos                        
                        $sql = "UPDATE pedidos p
                                    SET estado_pedido = :opcion
                                    FROM carrito c
                                    JOIN detalle_carrito dc ON c.id_carrito = dc.id_carrito
                                    WHERE p.id_carrito = c.id_carrito
                                    AND dc.id_detalle_carrito = :id_detalle_carrito;";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':opcion', $opcion_seleccionada);
                        $stmt->bindParam(':id_detalle_carrito', $id_detalle_carrito);
                        if ($stmt->execute()) {                            
                            if ($opcion_seleccionada == "Entregado") {
                                //calcular precio total
                                $sql = "SELECT SUM(dc.Precio) AS total
                                        FROM detalle_carrito dc
                                        JOIN pedidos ped ON dc.id_carrito = ped.id_carrito
                                        WHERE ped.id_pedido= :id_pedido ";
                                $stmt = $conn->prepare($sql);
                                $stmt->bindParam(':id_pedido', $id_pedido);  
                                $stmt->execute();                              
                                $total = $stmt->fetch();
                                echo $total['total'];
                                // Obtener la fecha y hora actual en formato Y-m-d H:i:s (Año-Mes-Día Hora:Minuto:Segundo)
                                $fecha_actual = date('Y-m-d H:i:s');
                                // Pago de pedido                        
                                $sql = "INSERT INTO pagos(id_pedido, tipopago, monto_total, fecha_pago) 
                                        values (:pedido ,'Efectivo',:total,:fecha);";
                                $stmt = $conn->prepare($sql);
                                $stmt->bindParam(':pedido', $id_pedido);
                                $stmt->bindParam(':fecha', $fecha_actual);
                                $stmt->bindParam(':total', $total['total']);
                                if ($stmt->execute()) {
                                    echo "<script>alert('Pedido y pago realizado con éxito');
                                            window.location.href = 'pedidos.php';  // Redirige a pedido</script>";
                                } else {
                                    echo "<script>alert('Error al realizar pago');
                                            window.location.href = 'pedidos.php';  // Redirige a pedido</script>";
                                }
                            }else{
                                echo "<script>alert('Pedido actualizado con éxito'); </script>";
                            }
                            echo "<script>window.location.href = 'pedidos.php';  // Redirige a pedido</script>";
                        } else {
                            echo "<script>alert('Error al actualizar');
                            window.location.href = 'pedidos.php';  // Redirige a pedido</script>";
                        }                        

                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- core  -->
    <script src="../assets/vendors/jquery/jquery-3.4.1.js"></script>
    <script src="../assets/vendors/bootstrap/bootstrap.bundle.js"></script>

    <!-- bootstrap affix -->
    <script src="../assets/vendors/bootstrap/bootstrap.affix.js"></script>
</body>

</html>