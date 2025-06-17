<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Detalle</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <!-- Stylesheet -->
    <link href="../assets/css/Style.css" rel="stylesheet">
    <link href="../assets/css/style_Detalle.css" rel="stylesheet">
</head>

<body data-spy="scroll" data-target=".navbar" data-offset="40" id="home">
    <!-- Navbar -->
    <?php
    // Inicia la sesión
    session_start();
    require_once "menu.php";
    if (isset($_GET['producto'])) {
        $id_productos = $_GET['producto'];
        $consultaProductos = "SELECT * FROM productos where id_productos= $id_productos";
    }
    // Ejecutar la consulta para obtener los productos        
    $resultado = $conn->prepare($consultaProductos);
    $resultado->execute();

    $consulta_tamaño = "SELECT * FROM precios where id_producto= $id_productos";
    // Ejecutar la consulta para obtener los precios        
    $stmt = $conn->prepare($consulta_tamaño);
    $stmt->execute();

    $consulta_extras = "SELECT * FROM extras where id_producto= $id_productos";
    // Ejecutar la consulta para obtener los extras        
    $stmt_extras = $conn->prepare($consulta_extras);
    $stmt_extras->execute();
    $precioTotal = 0;
    $tamaño = '';
    $id_precio = 0;
    $id_extra = null;
    $descripcion_extras = null;
    ?>

    <!-- Shop Detail Start -->
    <div class="container-fluid pb-5">
        <div class="row px-xl-5">
            <div class="col-lg-5 mb-30">

                <?php
                // Mostrar los resultados
                if ($resultado->rowCount() > 0) {
                    $producto = $resultado->fetch();
                    echo "
                <img class='w-100 h-100' src='" . $producto['imagen'] . "' alt='" . $producto['nombre'] . "'>
                    ";
                }
                ?>
            </div>

            <div class="col-lg-7 h-auto mb-30">
                <div class="h-100">
                    <h3><?php echo $producto['nombre']; ?></h3>
                    <h3 class="font-weight-semi-bold mb-4" id="total-price">$0</h3>
                    <p class="mb-4"><?php echo $producto['descripcion']; ?></p>
                    <form method='post'>
                        <?php
                        // Mostrar los precios por tamaño
                        if ($stmt->rowCount() > 0) {
                            echo "
                            <div class='d-flex mb-3'>
                                <strong class='mr-3'>Sizes:</strong>
                                ";
                            // Variable para controlar el primer radio button
                            $primerRadio = true;
                            while ($precio = $stmt->fetch()) {
                                // Determina si es el primer radio button
                                $checked = $primerRadio ? 'checked' : ''; // Si es el primero, asigna 'checked', sino deja vacío.
                                // Primer grupo de radio botones
                                echo "
                                    <div class='custom-control custom-radio custom-control-inline'>
                                        <input type='radio' class='custom-control-input' id='S" . $precio['id_precio'] . "' name='size' value='" . $precio['precio'] . "'  $checked>
                                        <label class='custom-control-label' for='S" . $precio['id_precio'] . "'>" . $precio['tamanio'] . "</label>
                                    </div>
                                ";

                                // Establecer la variable $primerRadio como false para las siguientes iteraciones
                                $primerRadio = false;
                                $precioTotal = $precio['precio'];
                            }
                            echo "
                            </div>
                            <div class='d-flex mb-3'>";
                            $stmt->execute();
                            $primerRadio = true;
                            while ($precio = $stmt->fetch()) {
                                // Determina si es el primer radio button
                                $checked = $primerRadio ? 'checked' : ''; // Si es el primero, asigna 'checked', sino deja vacío.
                                // segundo grupo de radio botones
                                echo "
                                    <div class='custom-control custom-radio custom-control-inline' style='display:none;'>
                                        <input type='radio' class='custom-control-input' id='T" . $precio['id_precio'] . "' name='size2' value='" . $precio['tamanio'] . "'  $checked >
                                        <label class='custom-control-label' for='T" . $precio['id_precio'] . "'>" . $precio['precio'] . "</label>
                                    </div>
                                ";

                                // Establecer la variable $primerRadio como false para las siguientes iteraciones
                                $primerRadio = false;
                                $precioTotal = $precio['precio'];
                            }
                            echo "
                            </div>
                            <div class='d-flex mb-3'>";
                            $stmt->execute();
                            $primerRadio = true;
                            while ($precio = $stmt->fetch()) {
                                // Determina si es el primer radio button
                                $checked = $primerRadio ? 'checked' : ''; // Si es el primero, asigna 'checked', sino deja vacío.
                                // tercer grupo de radio botones
                                echo "
                                    <div class='custom-control custom-radio custom-control-inline' style='display:none;'>
                                        <input type='radio' class='custom-control-input' id='I" . $precio['id_precio'] . "' name='size3' value='" . $precio['id_precio'] . "'  $checked>                                        
                                        <label class='custom-control-label' for='I" . $precio['id_precio'] . "'>" . $precio['id_precio'] . "</label>
                                    </div>
                                ";

                                // Establecer la variable $primerRadio como false para las siguientes iteraciones
                                $primerRadio = false;
                                $precioTotal = $precio['precio'];
                            }
                            echo "
                            </div>";
                        }
                        ?>
                        <?php
                        // Mostrar los precios por tamaño
                        if ($stmt_extras->rowCount() > 0) {
                            echo "
                            <div class='d-flex mb-4'>
                                <strong class='mr-3'>Extras:</strong>
                                ";
                            while ($extras = $stmt_extras->fetch()) {
                                echo "
                                <div class='custom-control custom-radio custom-control-inline'>
                                    <input type='checkbox' class='custom-control-input' id='E" . $extras['id_extra'] . "' name='extra[]' value='" . $extras['precio_extra'] . "'>
                                    <label class='custom-control-label' for='E" . $extras['id_extra'] . "'>" . $extras['nombre_extra'] . "</label>
                                </div>
                                    ";
                            }
                            echo "
                            </div>";
                        }
                        ?><!--cantidad y botones de incremento/decremento-->
                        <div class="d-flex align-items-center mb-4 pt-2">
                            <div class="input-group quantity mr-3" style="width: 130px;">
                                <div class="input-group-btn">
                                    <button type="button" class="btn btn-primary btn-minus" id="min">
                                        <i class="fa fa-minus">-</i>
                                    </button>
                                </div>
                                <input type="text" class="form-control text-dark bg-secondary border-0 text-center"
                                    value="1" id="quantity-input" name="quantity" required>
                                <div class="input-group-btn">
                                    <button type="button" class="btn btn-primary btn-plus" id="plus">
                                        <i class="fa fa-plus">+</i>
                                    </button>
                                </div>
                            </div>
                            <input type="text" id="id_extras"
                                name="id_extras"><!--aqui se obtiene el id del extra añadido-->
                            <input type="hidden" id="extrasSeleccionados"
                                name="extrasSeleccionados"><!--aqui se obtienen los extras añadidos-->
                            <button type="submit" class="btn btn-primary px-3" id="cart" name="cart"><i
                                    class="fa fa-shopping-cart mr-1">Add To Cart</i></button>
                        </div>

                    </form>
                    <?php
                    if (isset($_POST['cart'])) {
                        // Recoger los datos del formulario                        
                        $quantity = $_POST['quantity']; // Cantidad seleccionada
                        $size = $_POST['size']; // Tamaño seleccionado (precio)
                        $extras = 0; // Inicializamos la variable para la suma
                        $tamaño = $_POST['size2']; // Tamaño seleccionado (tamaño)
                        $id_precio = $_POST['size3']; // Tamaño seleccionado (id_precio)
                        $id_extra = $_POST['id_extras'];
                        $id_extra = isset($_POST['id_extras']) ? substr(trim($_POST['id_extras']), -1) : null;                        

                        // Verificamos si se han seleccionado opciones de extras
                        if (isset($_POST['extra'])) {
                            // Sumar los valores de los extras seleccionados
                            $extras = array_sum($_POST['extra']);
                            $descripcion_extras = $_POST['extrasSeleccionados']; // extras seleccionados (en formato de cadena)     
                        }
                        $size = ($size + $extras) * $quantity;
                        $sql_check = "SELECT carrito.id_carrito
                                        FROM carrito
                                        WHERE NOT EXISTS (
                                            SELECT 1
                                            FROM pedidos
                                            WHERE pedidos.id_carrito = carrito.id_carrito
                                        );";
                        $stmt_check = $conn->prepare($sql_check);
                        $stmt_check->execute();
                        /*
                        Realiza una consulta para ver si el carrito no a pasado a pedido, 
                        si es asi crear uno, si no seguir añadiendo productos al carrito a traves de detalle
                        */
                        if ($stmt_check->rowCount() > 0) {
                            $carrito = $stmt_check->fetch();

                            // Si está en pedido, insertar a detalle_carrito
                            $sql_insert = "INSERT INTO detalle_carrito (id_carrito, id_producto, cantidad, precio, id_precio, id_extra, descripcion_extras, tamanio) 
                                            VALUES (:id_carrito, :producto_id, :cantidad, :precio, :id_precio, :id_extra, :descripcion_extras, :sizes)";
                            $stmt_insert = $conn->prepare($sql_insert);
                            $stmt_insert->bindParam(':id_carrito', $carrito['id_carrito']);

                        } else {
                            //si no esta, insertar un nuevo carrito
                            $usuario = $_SESSION['id_usuario'];
                            // Obtener la fecha y hora actual en formato Y-m-d H:i:s (Año-Mes-Día Hora:Minuto:Segundo)
                            $fecha_actual = date('Y-m-d H:i:s');
                            $sql_insert = "INSERT INTO carrito (fecha_carrito, id_usuario) VALUES
                                            (:fecha, $usuario) RETURNING id_carrito;";
                            $stmt_insert_carrito = $conn->prepare($sql_insert);
                            $stmt_insert_carrito->bindParam(':fecha', $fecha_actual);
                            $stmt_insert_carrito->execute();
                            // Obtener el id del nuevo carrito insertado
                            $id_carrito = $stmt_insert_carrito->fetch(PDO::FETCH_ASSOC)['id_carrito'];

                            // insertar a detalle_carrito
                            $sql_insert = "INSERT INTO detalle_carrito (id_carrito, id_producto, cantidad, precio, id_precio, id_extra, descripcion_extras, tamanio) 
                                            VALUES (:id_carrito, :producto_id, :cantidad, :precio, :id_precio, :id_extra, :descripcion_extras, :sizes)";
                            $stmt_insert = $conn->prepare($sql_insert);
                            $stmt_insert->bindParam(':id_carrito', $id_carrito);
                        }
                        $stmt_insert->bindParam(':producto_id', $producto['id_productos']);
                        $stmt_insert->bindParam(':cantidad', $quantity);
                        $stmt_insert->bindParam(':precio', $size);
                        $stmt_insert->bindParam(':id_precio', $id_precio);
                        $stmt_insert->bindParam(':id_extra', $id_extra);
                        $stmt_insert->bindParam(':descripcion_extras', $descripcion_extras);
                        $stmt_insert->bindParam(':sizes', $tamaño);
                        if ($stmt_insert->execute()) {
                            // La consulta se ejecutó correctamente
                            echo "<script>alert('Producto insertado correctamente');</script>";
                        } else {
                            // Hubo un error al ejecutar la consulta
                            echo "<script>alert('Ocurrio un error!');</script>";
                        }
                    }

                    ?>
                </div>
            </div>
        </div>

    </div>
    </div>
    <!-- Shop Detail End -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let precioBase = <?php echo $precioTotal; ?>;  // Este valor se actualizará según el tamaño seleccionado
            let precioExtras = 0;  // Precio adicional de los extras seleccionados
            let precioTotal = 0; //variable para input y actualizacion de precio
            let id_precio = 0;
            // Variable para almacenar los nombres de los extras seleccionados
            let extrasSeleccionados = [];
            let id_extras = [];

            //incrementar cantidad input
            const btnMinus = document.getElementById("min");//boton mas
            const btnPlus = document.getElementById("plus");//boton menos
            const quantityInput = document.getElementById("quantity-input"); // Campo de cantidad

            // Función para actualizar el precio total
            function actualizarPrecio() {
                precioTotal = (precioBase + precioExtras) * quantityInput.value;  // El precio total es la suma del precio base y los extras  
                //cambia el contenido en pantalla mostrando el resultado de la operacion anterior con punto decimal              
                document.getElementById('total-price').textContent = '$' + precioTotal.toFixed(2);
                // Mostrar los extras seleccionados en el HTML
                document.querySelector("#extrasSeleccionados").value = extrasSeleccionados.join(", ");
                document.querySelector("#id_extras").value = id_extras;
            }

            // Actualizar el precio cuando se selecciona un tamaño
            document.querySelectorAll('input[name="size"]').forEach(function (sizeRadio) {
                sizeRadio.addEventListener('change', function () {
                    // Selecciona el radio correspondiente en el grupo 2 con el mismo id
                    document.querySelectorAll('input[name="size2"]').forEach(radio2 => {
                        if (radio2.id === 'T' + this.id.substring(1)) {
                            radio2.checked = true;
                        }
                    });
                    // Selecciona el radio correspondiente en el grupo 3 con el mismo id
                    document.querySelectorAll('input[name="size3"]').forEach(radio2 => {
                        if (radio2.id === 'I' + this.id.substring(1)) {
                            radio2.checked = true;
                        }
                    });
                    // Actualizamos el precio base del producto según el tamaño seleccionado
                    precioBase = parseFloat(this.value);  // El valor es el precio del tamaño                                                                             
                    actualizarPrecio();  // Actualizamos el precio en pantalla                    
                });
            });

            // Actualizar el precio y los extras cuando se seleccionan o deseleccionan extras
            document.querySelectorAll('input[name="extra[]"]').forEach(function (extraCheckbox) {
                extraCheckbox.addEventListener('change', function () {
                    // Obtener el label asociado al checkbox seleccionado
                    let label = document.querySelector("label[for='" + this.id + "']");

                    // Si el extra está seleccionado, sumamos su precio y añadimos el nombre del extra
                    if (this.checked) {
                        precioExtras += parseFloat(this.value);  // Sumar el precio del extra
                        if (label) {
                            extrasSeleccionados.push(label.innerText + ' $' + this.value);  // Añadir el nombre del extra
                            id_extras.push(this.id.substring(1));  // Añadir el id del extra
                        }
                    } else {
                        // Si el extra se deselecciona, restamos su precio y eliminamos el nombre del extra
                        precioExtras -= parseFloat(this.value);  // Restar el precio del extra
                        let index = extrasSeleccionados.indexOf(label.innerText + ' $' + this.value);
                        if (index > -1) {
                            extrasSeleccionados.splice(index, 1);  // Eliminar el nombre del extra
                            id_extras.splice(index, 1);  // Eliminar el id del extra
                        }
                    }
                    actualizarPrecio();  // Actualizamos el precio en pantalla
                });
            });


            // Llamar a la función cuando el valor del input cambie
            quantityInput.addEventListener("input", actualizarPrecio);

            // Función para disminuir la cantidad
            btnMinus.addEventListener("click", function () {
                let currentValue = parseInt(quantityInput.value); // Obtener el valor actual
                if (currentValue > 1) { // Asegurarse de que el valor no baje de 1
                    quantityInput.value = currentValue - 1;//decrementa 1 al valor actual en el campo de cantidad
                    actualizarPrecio();  // Actualizamos el precio en pantalla
                }
            });

            // Función para aumentar la cantidad
            btnPlus.addEventListener("click", function () {
                let currentValue = parseInt(quantityInput.value); // Obtener el valor actual
                quantityInput.value = currentValue + 1;//incrementa 1 al valor actual en el campo de cantidad
                actualizarPrecio();  // Actualizamos el precio en pantalla
            });

            // Llamamos a la función de actualización al cargar la página, para que se muestre el precio correcto
            actualizarPrecio();
        });

    </script>
    <!-- core  -->
    <script src="../assets/vendors/jquery/jquery-3.4.1.js"></script>
    <script src="../assets/vendors/bootstrap/bootstrap.bundle.js"></script>

    <!-- bootstrap affix -->
    <script src="../assets/vendors/bootstrap/bootstrap.affix.js"></script>
</body>

</html>