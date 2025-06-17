<?php
// Inicia la sesión
session_start();
require_once "menu_admin_sup.php";

// Verifica si la variable de sesión 'id_usuario' está configurada
if (!isset($_SESSION['id_usuario'])) {
    // Si no está configurada, redirige al login
    header('Location: ../index.php');
    exit(); // Asegúra de que el script se detenga después de redirigir
}

//verificar que enlace fue presionado
if (isset($_GET['productos'])) {
    switch ($_GET['productos']) {
        case 'editar':
            echo "
                <div class='container-fluid'>

                    <h1 class='h3 mb-2 text-gray-800'>Editar producto</h1>                    

                    <div class='card shadow mb-4'>
                        <div class='card-header py-3'>
                            <h6 class='m-0 font-weight-bold text-primary'>Datos</h6>
                        </div>
                        <form method='POST'>
                            <div width='20%' style='margin-left:20px;'>
                                <p class='m-0 font-weight-bold text-dark'>Ingrese el nombre: </p>
                                <input type='text' name='busqueda' id='busqueda'>
                                <button class='btn btn-info' type='submit' name='buscar'>Buscar</button>                                
                            </div>
                        </form>";
            // Verificar si se ha enviado el formulario
            if (isset($_POST['buscar'])) {
                // Obtener el valor de busqueda
                $nombre =$_POST['busqueda'];
                

                // Escapar el valor para evitar inyecciones SQL
                $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');

                // Ejecutar la consulta
                $consulta = "SELECT *
                            FROM productos pr
                            WHERE pr.nombre LIKE :nombre";
                $resultado = $conn->prepare($consulta);
                $resultado->bindValue(':nombre', "%$nombre%");
                $resultado->execute();

                // Mostrar los resultados
                if ($resultado->rowCount() > 0) {
                    $consulta = "SELECT * FROM categoria";
                    $stmt = $conn->prepare($consulta);
                    $stmt->execute();
                    $categoria = $stmt->fetchall();
                    echo "
                        <div class='card-body'>
                            <div class='table-responsive'>                                
                                <table class='table table-hover table-bordered text-dark' id='dataTable' width='90%' cellspacing='0'>
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th>Imagen</th>
                                            <th>Categoría</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>";

                    // obtiene los productos encontrados
                    $productos = $resultado->fetchAll();
                    foreach ($productos as $key => $value) {
                        echo "
                            <tr>
                                <form method='POST' enctype='multipart/form-data'>
                                <td><input type='text' name='nombre' id='nombre' value='" . $value['nombre'] . "'></td>
                                <td><input type='text' name='descripcion' id='descripcion' value='" . $value['descripcion'] . "'></td>
                                <td>
                                    <!-- Imagen actual con estilo -->
                                    <div class='image-preview'>
                                        <img id='" . $value['id_productos'] . "' 
                                            src='" . $value['imagen'] . "' class='rounded shadow-sm' width='80'>
                                    </div>

                                    <!-- Input para subir una nueva imagen -->
                                    <input type='file' name='imagen' id='imagen' class='' accept='image/*'></td>
                                <td> <select name='id_categoria'>";
                        foreach ($categoria as $key => $valor) {
                            // Comparamos si el id_categoria de la base de datos es igual al valor de la opción
                            $selected = ($value['id_categoria'] == $valor['id_categoria']) ? "selected" : "";
                            echo "
                                <option value='" . $valor['id_categoria'] . "' $selected>" . $valor['nombre_categoria'] . "</option>";
                        }
                        echo "      </select></td>
                                    <input type='hidden' name='imagen2' id='imagen2' value='" . $value['imagen'] . "'>
                                    <input type='hidden' name='id_producto' id='id_producto' value='" . $value['id_productos'] . "'>
                                <td><button class='btn btn-info' type='submit' name='actualizar'>Actualizar</button></td>
                                </form>
                                </tr>";
                    }
                    echo "                                                          
                                    </tbody>
                                </table>                                
                            </div>
                        </div>
                    </div>
                </div>";


                } else {
                    echo "<script>alert('No se encontraron productos con ese nombre')</script>";
                }
            }
            // Consulta para obtener productos con sus precios y extras
            $queryTotal = "
            SELECT 
                p.id_productos, 
                p.nombre, 
                pr.id_precio, 
                pr.tamanio, 
                pr.precio, 
                e.id_extra, 
                e.nombre_extra, 
                e.precio_extra
            FROM productos p
            LEFT JOIN precios pr ON p.id_productos = pr.id_producto
            LEFT JOIN extras e ON p.id_productos = e.id_producto
            ORDER BY p.id_productos, pr.id_precio, e.id_extra
            ";
            $productosCompleto = $conn->query($queryTotal)->fetchAll();
            //consulta para obtener los productos
            $query = "SELECT id_productos, nombre FROM productos";
            $productos = $conn->query($query)->fetchAll();
            ?><!--bloque de codigo para precios y extras -->

            <div class='container-fluid'>
                <h1 class='h3 mb-2 text-dark font-weight-bold'>Editar Precios y Extras por Producto</h1>
                <div class='card shadow mb-4'>
                    <div class='card-header py-3'>
                        <h6 class='m-0 font-weight-bold text-primary'>Datos</h6>
                    </div>
                    <div class='card-body'>
                        <div class='table-responsive'></div>
                        <form method="POST">
                            <label class="font-weight-bold text-dark" for="producto">Producto:</label>
                            <select name="id_producto" id="id_producto" required onchange="cargarDatosProducto()">
                                <option value="">Seleccione un producto</option>
                                <?php foreach ($productos as $producto) { ?>
                                    <option value="<?= $producto['id_productos'] ?>"><?= $producto['nombre'] ?></option>
                                <?php } ?>
                            </select>
                        </form>
                        <div id="datos_producto"></div>
                    </div>
                </div>
            </div>
            <?php
            try {
                // Obtener datos del formulario
                if (isset($_POST['guardarTamanio'])) {
                    $id_producto = $_POST['id_producto'];
                    $tamanios = $_POST['tamanios'] ?? [];
                    $precios = $_POST['precios'] ?? [];
                    $nombres_extra = $_POST['nombres_extra'] ?? [];
                    $precios_extra = $_POST['precios_extra'] ?? [];

                    $conn->beginTransaction(); // Iniciar una transacción

                    // Insertar precios y tamaños
                    $sqlPrecio = "INSERT INTO precios (id_producto, tamanio, precio) VALUES (:id_producto, :tamanio, :precio)";
                    $stmtPrecio = $conn->prepare($sqlPrecio);

                    foreach ($tamanios as $i => $tamanio) {
                        $stmtPrecio->execute([
                            ':id_producto' => $id_producto,
                            ':tamanio' => $tamanio,
                            ':precio' => $precios[$i]
                        ]);
                    }
                    // Insertar extras
                    $sqlExtra = "INSERT INTO extras (id_producto, nombre_extra, precio_extra) VALUES (:id_producto, :nombre_extra, :precio_extra)";
                    $stmtExtra = $conn->prepare($sqlExtra);

                    foreach ($nombres_extra as $i => $nombre_extra) {
                        $stmtExtra->execute([
                            ':id_producto' => $id_producto,
                            ':nombre_extra' => $nombre_extra,
                            ':precio_extra' => $precios_extra[$i]
                        ]);
                    }
                    echo "<script>alert('Datos insertados correctamente')</script>";
                    $conn->commit(); // Confirmar la transacción
                }
            } catch (PDOException $e) {
                if ($conn->inTransaction()) { // Verificar si hay una transacción activa antes de hacer rollback
                    $conn->rollBack(); // Revertir los cambios
                }
                die("Error al guardar los datos: " . $e->getMessage());
            }
            // **Proceso de actualización de datos**
            if (isset($_POST['guardarEdicion'])) {
                $conn->beginTransaction(); // Iniciar transacción para evitar inconsistencias                
                try {
                    $id_producto = $_POST['id_producto'];

                    // **Actualizar precios**
                    if (!empty($_POST['tamanios']) && !empty($_POST['precios'])) {
                        foreach ($_POST['tamanios'] as $id_precio => $tamanio) {
                            $precio = $_POST['precios'][$id_precio];
                            $sql = "UPDATE precios SET tamanio = :tamanio, precio = :precio WHERE id_precio = :id_precio";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute([
                                ':tamanio' => $tamanio,
                                ':precio' => $precio,
                                ':id_precio' => $id_precio
                            ]);
                        }
                    }

                    // **Actualizar extras**
                    if (!empty($_POST['nombres_extra']) && !empty($_POST['precios_extra'])) {
                        foreach ($_POST['nombres_extra'] as $id_extra => $nombre_extra) {
                            $precio_extra = $_POST['precios_extra'][$id_extra];
                            $sql = "UPDATE extras SET nombre_extra = :nombre_extra, precio_extra = :precio_extra WHERE id_extra = :id_extra";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute([
                                ':nombre_extra' => $nombre_extra,
                                ':precio_extra' => $precio_extra,
                                ':id_extra' => $id_extra
                            ]);
                        }
                    }

                    $conn->commit(); // Confirmar cambios en la base de datos
                    echo "<script>alert('Datos actualizados correctamente'); window.location.href='tables.php?productos=editar';</script>";
                } catch (PDOException $e) {
                    $conn->rollBack(); // Revertir cambios si ocurre un error
                    die("Error al actualizar los datos: " . $e->getMessage());
                }
            }
            // Verificar si se ha enviado el formulario
            if (isset($_POST['actualizar'])) {
                // Obtener los valores de los inputs
                $nombre = $_POST['nombre'];
                $descripcion = $_POST['descripcion'];                
                $id_categoria = $_POST['id_categoria'];
                $id_producto = $_POST['id_producto'];
                echo $id_producto;
                
                // Manejo de la imagen
                if (!empty($_FILES['imagen']['name'])) {
                    $directorioDestino = __DIR__ . "/../assets/imgs/"; // Carpeta donde se guardará la imagen                        

                    $nombreArchivo = basename($_FILES["imagen"]["name"]);
                    $rutaCompleta = $directorioDestino . $nombreArchivo; // Ruta física
                    $rutaBD = "../assets/imgs/" . $nombreArchivo; // Ruta relativa para la BD

                    // Verificar si el archivo es una imagen
                    $tipoArchivo = strtolower(pathinfo($rutaCompleta, PATHINFO_EXTENSION));
                    $formatosPermitidos = ["jpg", "jpeg", "png", "gif"];

                    if (in_array($tipoArchivo, $formatosPermitidos)) {
                        if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $rutaCompleta)) {
                            echo "<script>console.log('Imagen subida correctamente.');</script>";
                        } else {
                            die("❌ Error al mover la imagen.");
                        }
                    } else {
                        die("❌ Formato de imagen no permitido.");
                    }
                } else {
                    $rutaBD = $_POST['imagen2']; // Si no se sube imagen, guardamos NULL en la BD                    
                }

                $imagen =  $rutaBD;

                // Escapar los valores para evitar inyecciones SQL
                $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
                $descripcion = htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8');                
                $id_categoria = htmlspecialchars($id_categoria, ENT_QUOTES, 'UTF-8');

                // Ejecutar la consulta para actualizar los datos
                $consulta = "UPDATE productos SET
                        nombre = :nombre,
                        descripcion = :descripcion,
                        imagen = :imagen,
                        id_categoria = :id_categoria
                    WHERE id_productos = :id_producto";

                $resultado = $conn->prepare($consulta);

                // Vincular los valores a los parámetros de la consulta
                $resultado->bindParam(':nombre', $nombre);
                $resultado->bindParam(':descripcion', $descripcion);
                $resultado->bindParam(':imagen', $imagen);
                $resultado->bindParam(':id_categoria', $id_categoria);
                $resultado->bindParam(':id_producto', $id_producto);

                // Ejecutar la consulta
                if ($resultado->execute()) {
                    echo "<script>alert('Producto actualizado exitosamente')</script>";
                } else {
                    echo "<script>alert('Error al actualizar el Producto')</script>";
                }
            }
            break;

        case 'eliminar':
            echo "
                <div class='container-fluid'>

                    <h1 class='h3 mb-2 text-gray-800'>Eliminar producto</h1>                    

                    <div class='card shadow mb-4'>
                        <div class='card-header py-3'>
                            <h6 class='m-0 font-weight-bold text-primary'>Datos</h6>
                        </div>
                        <form method='POST'>
                            <div width='20%' style='margin-left:20px;'>
                                <p class='m-0 font-weight-bold text-dark' >Ingrese el nombre: </p>
                                <input type='text' name='busqueda' id='busqueda'>
                                <button class='btn btn-info' type='submit' name='buscar'>Buscar</button>
                            </div>
                        </form>";
            // Verificar si se ha enviado el formulario
            if (isset($_POST['buscar'])) {
                // Obtener el valor de busqueda
                $nombre = $_POST['busqueda'];

                // Escapar el valor para evitar inyecciones SQL
                $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');

                // Ejecutar la consulta
                $consulta = "SELECT 
                                pr.id_productos,
                                pr.nombre,
                                pr.descripcion,
                                pr.imagen,
                                c.nombre_categoria,
                                STRING_AGG(DISTINCT p.tamanio || ' ($' || p.precio::TEXT || ')', ', ') AS precios,
                                STRING_AGG(DISTINCT e.nombre_extra || ' ($' || e.precio_extra::TEXT || ')', ', ') AS extras
                            FROM productos pr
                            JOIN precios p ON pr.id_productos = p.id_producto
                            LEFT JOIN categoria c ON pr.id_categoria = c.id_categoria
                            LEFT JOIN extras e ON pr.id_productos = e.id_producto 
                            WHERE nombre LIKE :nombre
                            GROUP BY pr.id_productos, pr.nombre, pr.descripcion, pr.imagen, c.nombre_categoria;";
                $resultado = $conn->prepare($consulta);
                $resultado->bindValue(':nombre', "%$nombre%");
                $resultado->execute();

                // Mostrar los resultados
                if ($resultado->rowCount() > 0) {
                    echo "
                        <div class='card-body'>
                            <div class='table-responsive'>
                                <table class='table table-hover table-bordered text-dark' id='dataTable' width='90%' cellspacing='0'>
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th>Tamaño</th>
                                            <th>Extras</th>
                                            <th>Imagen</th>
                                            <th>Categoria</th>
                                            <th>Opción</th>
                                        </tr>
                                    </thead>
                                    <tbody>";
                    // muestra los productos encontrados
                    while ($producto = $resultado->fetch()) {
                        echo "
                        <form method='POST'>
                            <tr>
                                <td>" . $producto['nombre'] . "</td>
                                <td>" . $producto['descripcion'] . "</td>
                                <td>" . $producto['precios'] . "</td>
                                <td>" . $producto['extras'] . "</td>
                                <td>" . $producto['imagen'] . "</td>                                
                                <td>" . $producto['nombre_categoria'] . "</td>
                                <input type='hidden' name='id_productos' value='" . $producto['id_productos'] . "'>
                                <td><button class='btn btn-danger' type='submit' name='eliminar'>Eliminar</button></td>
                            </tr>
                        </form>";
                    }
                    echo "                                         
                                    </tbody>
                                </table>                                
                            </div>
                        </div>
                    </div>
                </div>";
                } else {
                    echo "<script>alert('No se encontraron productos con ese nombre')</script>";
                }
            }
            if (isset($_POST['eliminar'])) {
                $id_producto = $_POST['id_productos'];
                try {
                    $conn->beginTransaction(); // Iniciar transacción
                    // 1️⃣ Eliminar precios relacionados
                    $sqlPrecios = "DELETE FROM precios WHERE id_producto = :id_producto";
                    $stmtPrecios = $conn->prepare($sqlPrecios);
                    $stmtPrecios->execute([':id_producto' => $id_producto]);

                    // 2️⃣ Eliminar extras relacionados
                    $sqlExtras = "DELETE FROM extras WHERE id_producto = :id_producto";
                    $stmtExtras = $conn->prepare($sqlExtras);
                    $stmtExtras->execute([':id_producto' => $id_producto]);

                    // Escapar el valor para evitar inyecciones SQL
                    $id_producto = htmlspecialchars($id_producto, ENT_QUOTES, 'UTF-8');
                    // Ejecutar la consulta de busqueda para confirmar el usuario
                    $consulta = "DELETE FROM productos WHERE id_productos = :id_productos";
                    $resultado = $conn->prepare($consulta);
                    $resultado->bindValue(':id_productos', $id_producto);
                    $resultado->execute();
                    $conn->commit(); // Confirmar cambios
                    echo "<script>alert('✅ Producto eliminado correctamente')</script>";
                } catch (PDOException $e) {
                    $conn->rollBack(); // Revertir en caso de error
                    die("❌ Error al eliminar el producto: " . $e->getMessage());
                }

            }
            break;

        case 'añadir':
            //obtener las categorias
            $consulta = "SELECT * FROM categoria";
            $stmt = $conn->prepare($consulta);
            $stmt->execute();
            $categoria = $stmt->fetchall();
            echo "
                <div class='container-fluid'>

                    <h1 class='h3 mb-2 text-dark font-weight-bold'>Añadir producto</h1>                    

                    <div class='card shadow mb-4'>
                        <div class='card-header py-3'>
                            <h6 class='m-0 font-weight-bold text-primary'>Datos</h6>
                        </div>
                        <div class='card-body'>
                            <div class='table-responsive'>
                            <form  method='POST' enctype='multipart/form-data'>
                                <table class='table table-hover table-bordered text-dark' id='dataTable' width='90%' cellspacing='0'>
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th>Imagen</th>
                                            <th>Categoria</th>
                                        </tr>
                                    </thead>
                                    <tbody>                                        
                                        <tr>
                                            <td><input class='h6' type='text' name='nombre' id='nombre' ></td>
                                            <td><input class='h6' type='text' name='descripcion' id='descripcion' ></td>
                                            <td><input class='h6' type='file' name='imagen' id='imagen' accept='image/*'></td>
                                            <td> <select class='h6' name='categoria'>
                                                <option value='0'>Seleccione una opción</option>";
            foreach ($categoria as $key => $valor) {
                echo "
                                <option value='" . $valor['id_categoria'] . "'>" . $valor['nombre_categoria'] . "</option>";
            }
            echo "                          </select></td>
                                        </tr>                                                                                
                                    </tbody>
                                </table>
                                <button class='btn btn-success' type='submit' name='guardarArticulo'>Guardar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>";//aqui termina el div para añadir producto
            //consulta para obtener los productos
            $query = "SELECT id_productos, nombre FROM productos";
            $productos = $conn->query($query)->fetchAll();
            ?><!--bloque de codigo para precios y extras -->
            <div class='container-fluid'>
                <h1 class='h3 mb-2 text-dark font-weight-bold'>Añadir Precios y Extras por Producto</h1>
                <div class='card shadow mb-4'>
                    <div class='card-header py-3'>
                        <h6 class='m-0 font-weight-bold text-primary'>Datos</h6>
                    </div>
                    <div class='card-body'>
                        <div class='table-responsive'></div>
                        <form method="POST">
                            <label class="font-weight-bold text-dark" for="producto">Producto:</label>
                            <select name="id_producto" required>
                                <option value="">Seleccione un producto</option>
                                <?php foreach ($productos as $producto) { ?>
                                    <option value="<?= $producto['id_productos'] ?>"><?= $producto['nombre'] ?></option>
                                <?php } ?>
                            </select>

                            <h4 class="font-weight-bold text-dark">Tamaño</h>
                                <div id="contenedor-tamanios"></div>
                                <button class='btn' type="button" onclick="agregarTamanio()">➕ Agregar Tamaño</button>

                                <h4 class="font-weight-bold text-dark">Extra</h4>
                                <div id="contenedor-extras"></div>
                                <button class='btn' type="button" onclick="agregarExtra()">➕ Agregar Extra</button>

                                <br><br>
                                <button class='btn btn-success' name='guardarTamanio' type="submit">Guardar</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php
            try {
                // Obtener datos del formulario
                if (isset($_POST['guardarTamanio'])) {
                    $id_producto = $_POST['id_producto'];
                    $tamanios = $_POST['tamanios'] ?? [];
                    $precios = $_POST['precios'] ?? [];
                    $nombres_extra = $_POST['nombres_extra'] ?? [];
                    $precios_extra = $_POST['precios_extra'] ?? [];

                    $conn->beginTransaction(); // Iniciar una transacción

                    // Insertar precios y tamaños
                    $sqlPrecio = "INSERT INTO precios (id_producto, tamanio, precio) VALUES (:id_producto, :tamanio, :precio)";
                    $stmtPrecio = $conn->prepare($sqlPrecio);

                    foreach ($tamanios as $i => $tamanio) {
                        $stmtPrecio->execute([
                            ':id_producto' => $id_producto,
                            ':tamanio' => $tamanio,
                            ':precio' => $precios[$i]
                        ]);
                    }
                    // Insertar extras
                    $sqlExtra = "INSERT INTO extras (id_producto, nombre_extra, precio_extra) VALUES (:id_producto, :nombre_extra, :precio_extra)";
                    $stmtExtra = $conn->prepare($sqlExtra);

                    foreach ($nombres_extra as $i => $nombre_extra) {
                        $stmtExtra->execute([
                            ':id_producto' => $id_producto,
                            ':nombre_extra' => $nombre_extra,
                            ':precio_extra' => $precios_extra[$i]
                        ]);
                    }
                    echo "<script>alert('Datos insertados correctamente')</script>";
                    $conn->commit(); // Confirmar la transacción
                }
            } catch (PDOException $e) {
                if ($conn->inTransaction()) { // Verificar si hay una transacción activa antes de hacer rollback
                    $conn->rollBack(); // Revertir los cambios
                }
                die("Error al guardar los datos: " . $e->getMessage());
            }
            try {//bloque de codigo para articulos
                // Obtener datos del formulario
                if (isset($_POST['guardarArticulo'])) {
                    $nombre = $_POST['nombre'];
                    $id_categoria = $_POST['categoria'];
                    $descripcion = $_POST['descripcion'];

                    // Manejo de la imagen
                    if (!empty($_FILES['imagen']['name'])) {
                        $directorioDestino = __DIR__ . "/../assets/imgs/"; // Carpeta donde se guardará la imagen                        

                        $nombreArchivo = basename($_FILES["imagen"]["name"]);
                        $rutaCompleta = $directorioDestino . $nombreArchivo; // Ruta física
                        $rutaBD = "../assets/imgs/" . $nombreArchivo; // Ruta relativa para la BD

                        // Verificar si el archivo es una imagen
                        $tipoArchivo = strtolower(pathinfo($rutaCompleta, PATHINFO_EXTENSION));
                        $formatosPermitidos = ["jpg", "jpeg", "png", "gif"];

                        if (in_array($tipoArchivo, $formatosPermitidos)) {
                            if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $rutaCompleta)) {
                                echo "<script>console.log('Imagen subida correctamente.');</script>";
                            } else {
                                die("❌ Error al mover la imagen.");
                            }
                        } else {
                            die("❌ Formato de imagen no permitido.");
                        }
                    } else {
                        $rutaBD = null; // Si no se sube imagen, guardamos NULL en la BD
                    }

                    $conn->beginTransaction(); // Iniciar una transacción

                    // Escapar el valor para evitar inyecciones SQL
                    $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
                    $id_categoria = htmlspecialchars($id_categoria, ENT_QUOTES, 'UTF-8');
                    $descripcion = htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8');

                    // consulta
                    $sql = "INSERT INTO productos (nombre, descripcion, imagen, id_categoria) 
                                    VALUES (:nombre, :descripcion, :imagen, :id_categoria)";
                    // Preparar la consulta
                    $stmt = $conn->prepare($sql);

                    // Ejecutar la consulta con los valores correspondientes
                    $stmt->execute([
                        ':nombre' => $nombre,
                        ':descripcion' => $descripcion,
                        ':imagen' => $rutaBD,
                        ':id_categoria' => $id_categoria
                    ]);
                    echo "<script>alert('Producto insertado correctamente con ID: " . $conn->lastInsertId() . "')</script>";
                    $conn->commit(); // Confirmar la transacción
                }
            } catch (PDOException $e) {
                if ($conn->inTransaction()) { // Verificar si hay una transacción activa antes de hacer rollback
                    $conn->rollBack(); // Revertir los cambios
                }
                die("Error al guardar los datos: " . $e->getMessage());
            }
            break;
        case 'buscar':
            echo "
            <div class='container-fluid'>

                <h1 class='h3 mb-2 text-gray-800'>Productos</h1>                    

                <div class='card shadow mb-4'>
                    <div class='card-header py-3'>
                        <h6 class='m-0 font-weight-bold text-primary'>Datos</h6>
                    </div>
                    <form method='POST'>
                        <div width='20%' style='margin-left:20px;'>
                            <p class='m-0 font-weight-bold text-dark'>Ingrese el nombre: </p>
                            <input type='text' name='busqueda' id='busqueda'>
                            <button class='btn btn-info' type='submit' name='buscar'>Buscar</button>
                        </div>
                    </form>";
            // Verificar si se ha enviado el formulario
            if (isset($_POST['buscar'])) {
                // Obtener el valor de busqueda
                $nombre = $_POST['busqueda'];

                // Escapar el valor para evitar inyecciones SQL
                $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');

                // Ejecutar la consulta
                $consulta = "SELECT 
                                pr.id_productos,
                                pr.nombre,
                                pr.descripcion,
                                pr.imagen,
                                c.nombre_categoria,
                                STRING_AGG(DISTINCT p.tamanio || ' ($' || p.precio::TEXT || ')', ', ') AS precios,
                                STRING_AGG(DISTINCT e.nombre_extra || ' ($' || e.precio_extra::TEXT || ')', ', ') AS extras
                            FROM productos pr
                            LEFT JOIN precios p ON pr.id_productos = p.id_producto
                            LEFT JOIN categoria c ON pr.id_categoria = c.id_categoria
                            LEFT JOIN extras e ON pr.id_productos = e.id_producto 
                            WHERE nombre LIKE :nombre
                            GROUP BY pr.id_productos, pr.nombre, pr.descripcion, pr.imagen, c.nombre_categoria;";
                $resultado = $conn->prepare($consulta);
                $resultado->bindValue(':nombre', "%$nombre%");
                $resultado->execute();

                // Mostrar los resultados
                if ($resultado->rowCount() > 0) {
                    echo "
                    <div class='card-body'>
                        <div class='table-responsive'>
                            <table class='table table-hover table-bordered text-dark' id='dataTable' width='90%' cellspacing='0'>
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>tamaño</th>
                                        <th>Extras</th>
                                        <th>Imagen</th>                                        
                                        <th>Categoria</th>
                                    </tr>
                                </thead>
                                <tbody>";
                    // muestra los productos encontrados
                    while ($producto = $resultado->fetch()) {
                        echo "
                            <tr>
                                <td>" . $producto['nombre'] . "</td>
                                <td>" . $producto['descripcion'] . "</td>
                                <td>" . $producto['precios'] . "</td>
                                <td>" . $producto['extras'] . "</td>
                                <td>" . $producto['imagen'] . "</td>                                
                                <td>" . $producto['nombre_categoria'] . "</td>
                            </tr>";
                    }
                    echo "                                         
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>";
                } else {
                    echo "<script>alert('No se encontraron productos con ese nombre')</script>";
                }
            }
            break;
    }
}
if (isset($_GET['usuarios'])) {
    switch ($_GET['usuarios']) {
        case 'editar':
            echo "
                <div class='container-fluid'>
                    <h1 class='h3 mb-2 text-gray-800'>Editar usuario</h1>                    
                    <div class='card shadow mb-4'>
                        <div class='card-header py-3'>
                            <h6 class='m-0 font-weight-bold text-primary'>Datos</h6>
                        </div>
                        <form method='POST'>
                            <div width='20%' style='margin-left:20px;'>
                                <p class='m-0 font-weight-bold text-dark'>Ingrese el usuario: </p>
                                <input type='text' name='busqueda' id='busqueda'>
                                <button class='btn btn-info' type='submit' name='buscar'>Buscar</button>
                            </div>
                        </form>";
            // Verificar si se ha enviado el formulario
            if (isset($_POST['buscar'])) {
                // Obtener el valor de busqueda
                $nombre = $_POST['busqueda'];

                // Escapar el valor para evitar inyecciones SQL
                $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');

                // Ejecutar la consulta
                $consulta = "SELECT * FROM usuarios WHERE usuario LIKE :nombre";
                $resultado = $conn->prepare($consulta);
                $resultado->bindValue(':nombre', "%$nombre%");
                $resultado->execute();

                // Mostrar los resultados
                if ($resultado->rowCount() > 0) {
                    echo "
                    
                        <div class='card-body'>
                            <div class='table-responsive'>
                                <table class='table table-hover table-bordered text-dark' id='dataTable' width='90%' cellspacing='0'>
                                    <thead>
                                        <tr>
                                            <th>Usuario</th>
                                            <th>Correo</th>
                                            <th>Fecha de nacimiento</th>
                                            <th>Rol</th>
                                            <th>Nombre</th>
                                            <th>Apellido paterno</th>
                                            <th>Apellido Materno</th>
                                            <th>Opción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ";
                    // muestra los productos encontrados
                    $usuario = $resultado->fetchAll();
                    foreach ($usuario as $key => $value) {
                        echo "<tr>
                        <form method='POST'>
                            <td><input type='text' name='usuario' id='usuario' value='" . htmlspecialchars($value['usuario'], ENT_QUOTES, 'UTF-8') . "' required></td>
                            <td><input type='email' name='correo' id='correo' value='" . htmlspecialchars($value['e_mail'], ENT_QUOTES, 'UTF-8') . "' required></td>
                            <td><input type='date' name='fecha' id='fecha' value='" . htmlspecialchars($value['fechanac'], ENT_QUOTES, 'UTF-8') . "' required></td>
                            <td>
                            <select name='opcion'>
                                <option value='Administrador'" . (($value['rol'] == 'Administrador') ? 'selected' : '') . ">Administrador</option>
                                <option value='Vendedor'" . (($value['rol'] == 'Vendedor') ? 'selected' : '') . ">Vendedor</option>
                                <option value='Común'" . (($value['rol'] == 'Común') ? 'selected' : '') . ">Común</option>
                            </select></td>
                            <td><input type='text' name='nombre' id='nombre' value='" . htmlspecialchars($value['nombre'], ENT_QUOTES, 'UTF-8') . "' required></td>
                            <td><input type='text' name='apellidopat' id='apellidopat' value='" . htmlspecialchars($value['primerap'], ENT_QUOTES, 'UTF-8') . "' required></td>
                            <td><input type='text' name='apellidomat' id='apellidomat' value='" . htmlspecialchars($value['segundoap'], ENT_QUOTES, 'UTF-8') . "' required></td>
                            <td><button class='btn btn-info' type='submit' name='actualizar'>Actualizar</button></td>
                        </form> 
                            </tr>";
                    }
                    echo "                                          
                                    </tbody>
                                </table>                               
                            </div>
                        </div>
                    </div>
                </div>";

                } else {
                    echo "<script>alert('No se encontraron usuarios con ese nombre')</script>";
                }
            }
            // Verificar si se ha enviado el formulario de guardar
            if (isset($_POST['actualizar'])) {
                // Obtener los valores de los inputs
                $usuario = $_POST['usuario'];
                $correo = $_POST['correo'];
                $fecha = $_POST['fecha'];
                $rol = $_POST['opcion'];//rol
                $nombre = $_POST['nombre'];
                $apellidopat = $_POST['apellidopat'];
                $apellidomat = $_POST['apellidomat'];

                // Escapar los valores para evitar inyecciones SQL
                $usuario = htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8');
                $correo = htmlspecialchars($correo, ENT_QUOTES, 'UTF-8');
                $fecha = htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8');
                $rol = htmlspecialchars($rol, ENT_QUOTES, 'UTF-8');
                $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
                $apellidopat = htmlspecialchars($apellidopat, ENT_QUOTES, 'UTF-8');
                $apellidomat = htmlspecialchars($apellidomat, ENT_QUOTES, 'UTF-8');

                // Ejecutar la consulta para actualizar los datos
                $consulta = "UPDATE usuarios SET
                        e_mail = :correo,
                        fechanac = :fecha,
                        rol = :rol,
                        nombre = :nombre,
                        primerap = :apellidopat,
                        segundoap = :apellidomat
                    WHERE usuario = :usuario";

                $resultado = $conn->prepare($consulta);

                // Vincular los valores a los parámetros de la consulta
                $resultado->bindParam(':correo', $correo);
                $resultado->bindParam(':fecha', $fecha);
                $resultado->bindParam(':rol', $rol);
                $resultado->bindParam(':nombre', $nombre);
                $resultado->bindParam(':apellidopat', $apellidopat);
                $resultado->bindParam(':apellidomat', $apellidomat);
                $resultado->bindParam(':usuario', $usuario);

                // Ejecutar la consulta
                if ($resultado->execute()) {
                    echo "<script>alert('Usuario actualizado exitosamente')</script>";
                } else {
                    echo "<script>alert('Error al actualizar el usuario')</script>";
                }
            }

            break;
        case 'eliminar':
            echo "
                <div class='container-fluid'>
                    <h1 class='h3 mb-2 text-gray-800'>Eliminar usuario</h1>                    
                    <div class='card shadow mb-4'>
                        <div class='card-header py-3'>
                            <h6 class='m-0 font-weight-bold text-primary'>Datos</h6>
                        </div>
                        <form method='POST'>
                            <div width='20%' style='margin-left:20px;'>
                                <p class='m-0 font-weight-bold text-dark'>Ingrese el usuario: </p>
                                <input type='text' name='busqueda' id='busqueda'>
                                <button class='btn btn-info' type='submit' name='buscar'>Buscar</button>
                            </div>
                        </form>";
            // Verificar si se ha enviado el formulario
            if (isset($_POST['buscar'])) {
                // Obtener el valor de busqueda
                $nombre = $_POST['busqueda'];

                // Escapar el valor para evitar inyecciones SQL
                $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');

                // Ejecutar la consulta
                $consulta = "SELECT * FROM usuarios WHERE usuario LIKE :nombre";
                $resultado = $conn->prepare($consulta);
                $resultado->bindValue(':nombre', "%$nombre%");
                $resultado->execute();

                // Mostrar los resultados
                if ($resultado->rowCount() > 0) {
                    echo "
                        <div class='card-body'>
                            <div class='table-responsive'>
                                <table class='table table-hover table-bordered text-dark' id='dataTable' width='90%' cellspacing='0'>
                                    <thead>
                                        <tr>
                                            <th>Usuario</th>
                                            <th>Correo</th>
                                            <th>Fecha de nacimiento</th>
                                            <th>Rol</th>
                                            <th>Nombre</th>
                                            <th>Apellido paterno</th>
                                            <th>Apellido Materno</th>
                                            <th>Opción</th>
                                        </tr>
                                    </thead>
                                    <tbody>";
                    // muestra los productos encontrados
                    while ($usuario = $resultado->fetch()) {
                        echo "
                        <form method='POST'>
                            <tr>
                                <td>" . $usuario['usuario'] . "</td>
                                <td>" . $usuario['e_mail'] . "</td>
                                <td>" . $usuario['fechanac'] . "</td>
                                <td>" . $usuario['rol'] . "</td>
                                <td>" . $usuario['nombre'] . "</td>
                                <td>" . $usuario['primerap'] . "</td>
                                <td>" . $usuario['segundoap'] . "</td>
                                <input type='hidden' name='id_usuario' value='" . $usuario['id_usuario'] . "'>
                                <td><button class='btn btn-danger' type='submit' name='eliminar'>Eliminar</button></td>
                            </tr>
                        </form>";
                    }
                    echo " 
                                                                                
                                    </tbody>
                                </table>                                
                            </div>
                        </div>
                    </div>
                </div>";
                } else {
                    echo "<script>alert('No se encontraron usuarios con ese nombre')</script>";
                }
            }
            if (isset($_POST['eliminar'])) {
                $id_usuario = $_POST['id_usuario'];

                // Escapar el valor para evitar inyecciones SQL
                $id_usuario = htmlspecialchars($id_usuario, ENT_QUOTES, 'UTF-8');
                // Ejecutar la consulta de busqueda para confirmar el usuario
                $consulta = "DELETE FROM Usuarios WHERE id_usuario = :id_usuario";
                $resultado = $conn->prepare($consulta);
                $resultado->bindValue(':id_usuario', $id_usuario);

                // Mostrar los resultados
                if ($resultado->execute()) {
                    echo "<script>alert('Usuario eliminado con exito')</script>";
                }
            }
            break;

        case 'añadir':
            echo "
            <div class='container-fluid'>

                <h1 class='h3 mb-2 text-gray-800'>Añadir Usuario</h1>                    

                <div class='card shadow mb-4'>
                    <div class='card-header py-3'>
                        <h6 class='m-0 font-weight-bold text-primary'>Datos</h6>
                    </div>
                    <div class='card-body'>
                        <div class='table-responsive'>
                        <form method='POST'>
                            <table class='table table-hover table-bordered text-dark' id='dataTable' width='90%' cellspacing='0'>
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Contraseña</th>
                                        <th>Correo</th>
                                        <th>Fecha de nacimiento</th>
                                        <th>Rol</th>
                                        <th>Nombre</th>
                                        <th>Apellido paterno</th>
                                        <th>Apellido Materno</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type='text' name='usuario' id='usuario'  required></td>
                                        <td><input type='password' name='contraseña' id='contraseña'  required></td>
                                        <td><input type='e-mail' name='correo' id='correo'  required></td>
                                        <td><input type='date' name='fechanac' id='fechanac' required></td>
                                        <td>
                                            <select name='rol' required>
                                                <option value='Administrador'>Administrador</option>
                                                <option value='Vendedor'>Vendedor</option>
                                                <option value='Común'>Común</option>
                                            </select></td>
                                        </td>
                                        <td><input type='text' name='nombre' id='nombre'  required></td>
                                        <td><input type='text' name='primerap' id='primerap' required></td>
                                        <td><input type='text' name='segundoap' id='segundoap' required></td> 
                                    </tr>                                        
                                </tbody>
                            </table>
                            <button class='btn btn-success' type='submit' name='guardar'>Guardar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>";
            if (isset($_POST['guardar'])) {
                // Obtener el valor del formulario
                $usuario = $_POST['usuario'];
                $correo = $_POST['correo'];
                $contraseña = $_POST['contraseña'];
                $rol = $_POST['rol'];
                $fecha = $_POST['fechanac'];
                $nombre = $_POST['nombre'];
                $primer_apellido = $_POST['primerap'];
                $segundo_apellido = $_POST['segundoap'];

                // Escapar el valor para evitar inyecciones SQL
                $usuario = htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8');
                $correo = htmlspecialchars($correo, ENT_QUOTES, 'UTF-8');

                // Ejecutar la consulta de busqueda para confirmar el usuario
                $consulta = "SELECT usuario FROM usuarios 
                    where usuario=:usuario";
                $resultado = $conn->prepare($consulta);
                $resultado->bindValue(':usuario', $usuario);
                $resultado->execute();

                // Mostrar los resultados
                if ($resultado->rowCount() > 0) {
                    echo "<script>alert('El usuario ya existe, favor de ingresar otro')</script>";
                    return;
                }
                // Ejecutar la consulta de busqueda para confirmar el correo
                $consulta = "SELECT usuario, e_mail FROM usuarios 
                    where e_mail=:correo";
                $resultado = $conn->prepare($consulta);
                $resultado->bindValue(':correo', $correo);
                $resultado->execute();

                // Mostrar los resultados
                if ($resultado->rowCount() > 0) {
                    echo "<script>alert('El correo ya existe, favor de ingresar otro')</script>";
                    return;
                }

                // Escapar el valor para evitar inyecciones SQL
                $contraseña = htmlspecialchars($contraseña, ENT_QUOTES, 'UTF-8');
                $rol = htmlspecialchars($rol, ENT_QUOTES, 'UTF-8');
                $fecha = htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8');
                $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
                $primer_apellido = htmlspecialchars($primer_apellido, ENT_QUOTES, 'UTF-8');
                $segundo_apellido = htmlspecialchars($segundo_apellido, ENT_QUOTES, 'UTF-8');

                // Ejecutar la consulta
                $consulta = "INSERT INTO Usuarios ( usuario, e_mail, contraseña, rol, fechanac, nombre, 
                    primerap, segundoap) VALUES (:usuario, :correo, :contra, :rol,                    
                    :fecha, :nombre,:primer_apellido, :segundo_apellido);";
                $resultado = $conn->prepare($consulta);
                $resultado->bindValue(':usuario', $usuario);
                $resultado->bindValue(':correo', $correo);
                $resultado->bindValue(':contra', $contraseña);
                $resultado->bindValue(':rol', $rol);
                $resultado->bindValue(':fecha', $fecha);
                $resultado->bindValue(':nombre', $nombre);
                $resultado->bindValue(':primer_apellido', $primer_apellido);
                $resultado->bindValue(':segundo_apellido', $segundo_apellido);
                // Ejecutar la consulta
                if ($resultado->execute()) {
                    echo "<script>alert('Usuario creado con exito')</script>";
                } else {
                    echo "<script>alert('Error al crear el usuario')</script>";
                }

            }
            break;
        case 'buscar':
            echo "
                <div class='container-fluid'>
                    <h1 class='h3 mb-2 text-gray-800'>Buscar usuario</h1>                    
                    <div class='card shadow mb-4'>
                        <div class='card-header py-3'>
                            <h6 class='m-0 font-weight-bold text-primary'>Datos</h6>
                        </div>
                        <form method='POST'>
                            <div width='20%' style='margin-left:20px;'>
                                <p class='m-0 font-weight-bold text-dark'>Ingrese el usuario: </p>
                                <input type='text' name='busqueda' id='busqueda'>
                                <button class='btn btn-info' type='submit' name='buscar'>Buscar</button>                                
                            </div>
                        </form>";
            // Verificar si se ha enviado el formulario
            if (isset($_POST['buscar'])) {
                // Obtener el valor de busqueda
                $nombre = $_POST['busqueda'];

                // Escapar el valor para evitar inyecciones SQL
                $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');

                // Ejecutar la consulta
                $consulta = "SELECT * FROM usuarios WHERE usuario LIKE :nombre";
                $resultado = $conn->prepare($consulta);
                $resultado->bindValue(':nombre', "%$nombre%");
                $resultado->execute();

                // Mostrar los resultados
                if ($resultado->rowCount() > 0) {
                    echo "
                        <div class='card-body'>
                            <div class='table-responsive'>
                                <table class='table table-hover table-bordered text-dark' id='dataTable' width='90%' cellspacing='0'>
                                    <thead>
                                        <tr>
                                            <th>Usuario</th>
                                            <th>Correo</th>
                                            <th>Fecha de nacimiento</th>
                                            <th>Rol</th>
                                            <th>Nombre</th>
                                            <th>Apellido paterno</th>
                                            <th>Apellido Materno</th>
                                        </tr>
                                    </thead>
                                    <tbody>";
                    // muestra los productos encontrados
                    while ($usuario = $resultado->fetch()) {
                        echo "
                        <tr>
                        <td>" . $usuario['usuario'] . "</td>
                        <td>" . $usuario['e_mail'] . "</td>
                        <td>" . $usuario['fechanac'] . "</td>
                        <td>" . $usuario['rol'] . "</td>
                        <td>" . $usuario['nombre'] . " </td>
                        <td>" . $usuario['primerap'] . "</td>
                        <td>" . $usuario['segundoap'] . "</td>
                        </tr>
                    ";
                    }
                    echo " 
                                        </tr>                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>";

                } else {
                    echo "<script>alert('No se encontraron usuarios con ese nombre')</script>";
                }
            }
            break;
    }
}
if (isset($_GET['categorias'])) {
    switch ($_GET['categorias']) {
        case 'editar':
            echo "
                <div class='container-fluid'>
                    <h1 class='h3 mb-2 text-gray-800'>Editar categoria</h1>                    
                    <div class='card shadow mb-4'>
                        <div class='card-header py-3'>
                            <h6 class='m-0 font-weight-bold text-primary'>Datos</h6>
                        </div>
                        <form method='POST'>
                            <div width='20%' style='margin-left:20px;'>
                                <p class='m-0 font-weight-bold text-dark'>Ingrese la categoria: </p>
                                <input type='text' name='busqueda' id='busqueda'>
                                <button class='btn btn-info' type='submit' name='buscar'>Buscar</button>
                            </div>
                        </form>";
            // Verificar si se ha enviado el formulario
            if (isset($_POST['buscar'])) {
                // Obtener el valor de busqueda
                $nombre = $_POST['busqueda'];

                // Escapar el valor para evitar inyecciones SQL
                $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');

                // Ejecutar la consulta
                $consulta = "SELECT 
                                c1.id_categoria,
                                c1.nombre_categoria,
                                c1.id_categoria_padre,
                                c2.nombre_categoria AS nombre_categoria_padre
                            FROM 
                                categoria c1
                            LEFT JOIN 
                                categoria c2 ON c1.id_categoria_padre = c2.id_categoria
                            WHERE c1.nombre_categoria LIKE :nombre";
                $resultado = $conn->prepare($consulta);
                $resultado->bindValue(':nombre', "%$nombre%");
                $resultado->execute();

                // Mostrar los resultados
                if ($resultado->rowCount() > 0) {
                    echo "
                        <div class='card-body'>
                            <div class='table-responsive'>
                                <table class='table table-hover table-bordered text-dark' id='dataTable' width='90%' cellspacing='0'>
                                    <thead>
                                        <tr>
                                            <th>Categoria</th>
                                            <th>Categoria padre</th>
                                            <th>Opción</th>
                                        </tr>
                                    </thead>
                                    <tbody>";

                    // muestra los productos encontrados
                    while ($cat = $resultado->fetch()) {
                        // Ejecutar la consulta de busqueda categorias
                        $consulta = "SELECT * FROM categoria";
                        $categoria = $conn->prepare($consulta);
                        $categoria->execute();
                        echo "
                        <form method='POST'>    
                            <tr>
                                <td><input type='text' name='nombre_categoria' id='nombre_categoria' value=" . $cat['nombre_categoria'] . " required></td>
                                <td> <select name='categoria'>
                                        <option value='0' selected>Seleccione una opción</option>";
                        foreach ($categoria as $key => $valor) {
                            $selected = ($cat['id_categoria_padre'] == $valor['id_categoria']) ? "selected" : "";
                            echo "<option value='" . $valor['id_categoria'] . "' $selected>" . $valor['nombre_categoria'] . "</option>";
                        }
                        echo "                  </select></td>
                            <input type='hidden' name='id_categoria' value='" . $cat['id_categoria'] . "'>
                                <td><button class='btn btn-info' type='submit' name='actualizar'>Actualizar</button></td>
                            </tr>                            
                        </form>";
                    }
                    echo "                                         
                                    </tbody>
                                </table>                                
                            </div>
                        </div>
                    </div>

                </div>";
                } else {
                    echo "<script>alert('No se encontraron categorias con ese nombre')</script>";
                }
            }
            if (isset($_POST['actualizar'])) {
                // Obtener el valor del formulario
                $nombre = $_POST['nombre_categoria'];
                $categoria = $_POST['categoria'];
                $id_categoria = $_POST['id_categoria'];

                // Escapar el valor para evitar inyecciones SQL
                $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
                $categoria = htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8');
                $id_categoria = htmlspecialchars($id_categoria, ENT_QUOTES, 'UTF-8');

                // Ejecutar la consulta de busqueda para confirmar la categoria
                $consulta = "SELECT * FROM categoria 
                    where nombre_categoria=:nombre";
                $resultado = $conn->prepare($consulta);
                $resultado->bindValue(':nombre', $nombre);
                $resultado->execute();

                // Mostrar los resultados
                if ($resultado->rowCount() > 1) {
                    echo "<script>alert('Categoria existente, favor de ingresar otra')</script>";
                    break;
                }
                if ($categoria == 0) {
                    $categoria = null;
                }

                $consulta = "UPDATE categoria SET
                        nombre_categoria = :nombre, 
                        id_categoria_padre= :id_padre
                        WHERE id_categoria = :id_categoria;";
                // Ejecutar la consulta                
                $resultado = $conn->prepare($consulta);
                $resultado->bindValue(':nombre', $nombre);
                $resultado->bindValue(':id_padre', $categoria);
                $resultado->bindValue(':id_categoria', $id_categoria);
                // Ejecutar la consulta
                if ($resultado->execute()) {
                    echo "<script>alert('Categoria actualizada exitosamente')</script>";
                } else {
                    echo "<script>alert('Error al actualizar la categoria')</script>";
                }
            }
            break;

        case 'eliminar':
            echo "
                <div class='container-fluid'>
                    <h1 class='h3 mb-2 text-gray-800'>Eliminar categoria</h1>                    
                    <div class='card shadow mb-4'>
                        <div class='card-header py-3'>
                            <h6 class='m-0 font-weight-bold text-primary'>Datos</h6>
                        </div>
                        <form method='POST'>
                            <div width='20%' style='margin-left:20px;'>
                                <p class='m-0 font-weight-bold text-dark'>Ingrese la categoria: </p>
                                <input type='text' name='busqueda' id='busqueda'>
                                <button class='btn btn-info' type='submit' name='buscar'>Buscar</button>
                            </div>
                        </form>";
            // Verificar si se ha enviado el formulario
            if (isset($_POST['buscar'])) {
                // Obtener el valor de busqueda
                $nombre = $_POST['busqueda'];

                // Escapar el valor para evitar inyecciones SQL
                $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');

                // Ejecutar la consulta
                $consulta = "SELECT * FROM categoria WHERE nombre_categoria LIKE :nombre";
                $resultado = $conn->prepare($consulta);
                $resultado->bindValue(':nombre', "%$nombre%");
                $resultado->execute();

                // Mostrar los resultados
                if ($resultado->rowCount() > 0) {
                    echo "
                        <div class='card-body'>
                            <div class='table-responsive'>
                                <table class='table table-hover table-bordered text-dark' id='dataTable' width='90%' cellspacing='0'>
                                    <thead>
                                        <tr>
                                            <th>Id_categoria</th>
                                            <th>Nombre</th>
                                            <th>Opción</th>
                                        </tr>
                                    </thead>
                                    <tbody>";
                    // muestra los productos encontrados
                    while ($categoria = $resultado->fetch()) {
                        echo "
                        <form method='POST'>
                            <tr>
                                <td>" . $categoria['id_categoria'] . "</td>
                                <td>" . $categoria['nombre_categoria'] . "</td>
                                <input type='hidden' name='categoria' value='" . $categoria['id_categoria'] . "'>
                                <td><button class='btn btn-danger' type='submit' name='eliminar'>Eliminar</button></td>
                            </tr>
                        </form>";
                    }
                    echo "                                         
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>";

                } else {
                    echo "<script>alert('No se encontraron categorias con ese nombre')</script>";
                }
            }
            if (isset($_POST['eliminar'])) {
                $id_categoria = $_POST['categoria'];

                // Escapar el valor para evitar inyecciones SQL
                $id_categoria = htmlspecialchars($id_categoria, ENT_QUOTES, 'UTF-8');
                // Ejecutar la consulta de busqueda para confirmar el usuario
                $consulta = "DELETE FROM categoria WHERE id_categoria = :id_categoria";
                $resultado = $conn->prepare($consulta);
                $resultado->bindValue(':id_categoria', $id_categoria);

                // Mostrar los resultados
                if ($resultado->execute()) {
                    echo "<script>alert('Categoria eliminada con exito')</script>";
                }
            }
            break;

        case 'añadir':
            // Ejecutar la consulta de busqueda categorias
            $consulta = "SELECT * FROM categoria";
            $categoria = $conn->prepare($consulta);
            $categoria->execute();
            echo "
            <div class='container-fluid'>

                <h1 class='h3 mb-2 text-gray-800'>Añadir categoria</h1>                    

                <div class='card shadow mb-4'>
                    <div class='card-header py-3'>
                        <h6 class='m-0 font-weight-bold text-primary'>Datos</h6>
                    </div>
                    <div class='card-body'>
                        <div class='table-responsive'>
                        <form method='POST'>
                            <table class='table table-hover table-bordered text-dark' id='dataTable' width='90%' cellspacing='0'>
                                <thead>
                                    <tr>
                                            <th>Nombre</th>
                                            <th>Categoria padre</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type='text' name='nombre' id='nombre'  required></td>
                                        <td> <select name='categoria'>
                                                <option value='0' >Seleccione una opción</option>";

            foreach ($categoria as $key => $valor) {
                echo "<option value='" . $valor['id_categoria'] . "' >" . $valor['nombre_categoria'] . "</option>";
            }
            echo "                  </select></td>
                                    </tr>                                                                           
                                </tbody>
                            </table>  
                            <button class='btn btn-success' type='submit' name='guardar'>Guardar</button>
                            </form>                           
                        </div>
                    </div>
                </div>
            </div>";
            if (isset($_POST['guardar'])) {
                // Obtener el valor del formulario
                $nombre = $_POST['nombre'];
                $categoria = $_POST['categoria'];

                // Escapar el valor para evitar inyecciones SQL
                $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
                $categoria = htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8');

                // Ejecutar la consulta de busqueda para confirmar la categoria
                $consulta = "SELECT * FROM categoria 
                    where nombre_categoria=:nombre";
                $resultado = $conn->prepare($consulta);
                $resultado->bindValue(':nombre', $nombre);
                $resultado->execute();

                // Mostrar los resultados
                if ($resultado->rowCount() > 0) {
                    echo "<script>alert('Categoria existente, favor de ingresar otra')</script>";
                    return;
                }
                if ($categoria == 0) {
                    $categoria = null;
                }
                $consulta = "INSERT INTO categoria ( nombre_categoria, id_categoria_padre) 
                        VALUES (:nombre, :id_padre);";
                // Ejecutar la consulta                
                $resultado = $conn->prepare($consulta);
                $resultado->bindValue(':nombre', $nombre);
                $resultado->bindValue(':id_padre', $categoria);
                // Ejecutar la consulta
                if ($resultado->execute()) {
                    echo "<script>alert('Categoria creada exitosamente')</script>";
                } else {
                    echo "<script>alert('Error al crear la categoria')</script>";
                }
            }
            break;
        case 'buscar':
            echo "
                <div class='container-fluid'>
                    <h1 class='h3 mb-2 text-gray-800'>Categoria</h1>                    
                    <div class='card shadow mb-4'>
                        <div class='card-header py-3'>
                            <h6 class='m-0 font-weight-bold text-primary'>Datos</h6>
                        </div>
                        <form method='POST'>
                            <div width='20%' style='margin-left:20px;'>
                                <p class='m-0 font-weight-bold text-dark'>Ingrese la categoria: </p>
                                <input type='text' name='busqueda' id='busqueda'>
                                <button class='btn btn-info' type='submit' name='buscar'>Buscar</button>
                            </div>
                        </form>";
            // Verificar si se ha enviado el formulario
            if (isset($_POST['buscar'])) {
                // Obtener el valor de busqueda
                $nombre = $_POST['busqueda'];

                // Escapar el valor para evitar inyecciones SQL
                $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');

                // Ejecutar la consulta
                $consulta = "SELECT 
                                c1.id_categoria,
                                c1.nombre_categoria,
                                c1.id_categoria_padre,
                                c2.nombre_categoria AS nombre_categoria_padre
                            FROM 
                                categoria c1
                            LEFT JOIN 
                                categoria c2 ON c1.id_categoria_padre = c2.id_categoria
                            WHERE c1.nombre_categoria LIKE :nombre ORDER BY id_categoria";
                $resultado = $conn->prepare($consulta);
                $resultado->bindValue(':nombre', "%$nombre%");
                $resultado->execute();

                // Mostrar los resultados
                if ($resultado->rowCount() > 0) {
                    echo "
                        <div class='card-body'>
                            <div class='table-responsive'>
                                <table class='table table-hover table-bordered text-dark' id='dataTable' width='90%' cellspacing='0'>
                                    <thead>
                                        <tr>
                                            <th>Id_categoria</th>
                                            <th>Nombre</th>
                                            <th>Categoria padre</th>
                                        </tr>
                                    </thead>
                                    <tbody>";
                    // muestra los productos encontrados
                    while ($usuario = $resultado->fetch()) {
                        echo "
                        <tr>
                            <td>" . $usuario['id_categoria'] . " </td>
                            <td>" . $usuario['nombre_categoria'] . "</td>
                            <td>" . $usuario['nombre_categoria_padre'] . "</td>
                        </tr>";
                    }
                    echo "                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>";
                } else {
                    echo "<script>alert('No se encontraron categorias con ese nombre')</script>";
                }
            }
            break;
    }
}
if (isset($_GET['pedidos'])) {
    switch ($_GET['pedidos']) {
        case 'editar':
            echo "
                <div class='container-fluid'>
                    <h1 class='h3 mb-2 text-gray-800'>Editar pedido</h1>                    
                    <div class='card shadow mb-4'>
                        <div class='card-header py-3'>
                            <h6 class='m-0 font-weight-bold text-primary'>Datos</h6>
                        </div>
                        <form method='POST'>
                            <div width='20%' style='margin-left:20px;'>
                                <p class='m-0 font-weight-bold text-dark'>Ingrese el identificador: </p>
                                <input type='number' name='busqueda' id='busqueda'>
                                <button class='btn btn-info' type='submit' name='buscar'>Buscar</button>
                            </div>
                        </form>";
            // Verificar si se ha enviado el formulario
            if (isset($_POST['buscar'])) {
                // Obtener el valor de busqueda
                $busqueda = $_POST['busqueda'];

                // Escapar el valor para evitar inyecciones SQL
                $busqueda = htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8');

                // Verificar si el parámetro busqueda está definido
                $busqueda = !empty($_POST['busqueda']) ? $_POST['busqueda'] : null;

                //parte fija de la consulta
                $consulta = "SELECT dc.*, p.nombre, p.imagen, ped.id_pedido, ped.estado_pedido, ped.fecha_pedido, 
                        CONCAT(u.nombre, ' ', u.primerap, ' ', u.segundoap) AS nombre_completo 
                    FROM detalle_carrito dc
                    JOIN productos p ON dc.id_producto = p.id_productos
                    JOIN carrito c ON dc.id_carrito = c.id_carrito
                    JOIN pedidos ped ON c.id_carrito = ped.id_carrito
                    JOIN usuarios u ON c.id_usuario = u.id_usuario ";

                // Si está presente, agregamos el WHERE
                if ($busqueda) {
                    $consulta .= " WHERE ped.id_pedido = :pedido ";
                }
                $consulta .= " ORDER BY id_pedido ";
                $resultado = $conn->prepare($consulta);
                // Si está presente, bind el parámetro
                if ($busqueda) {
                    $resultado->bindValue(':pedido', $busqueda);
                }
                $resultado->execute();

                // Mostrar los resultados
                if ($resultado->rowCount() > 0) {
                    echo "
                        <div class='card-body'>
                            <div class='table-responsive'>
                                <table class='table table-hover table-bordered text-dark' id='dataTable' width='90%' cellspacing='0'>
                                    <thead>
                                        <tr>
                                            <th>Pedido</th>
                                            <th>Usuario</th>
                                            <th>Producto</th>
                                            <th>Tamaño</th>
                                            <th>Cantidad</th>
                                            <th>Extras</th>
                                            <th>Fecha</th>
                                            <th>Estado</th>
                                            <th>Opción</th>
                                        </tr>
                                    </thead>
                                    <tbody>";
                    $consulta = "SELECT * FROM productos";
                    $productos = $conn->prepare($consulta);

                    $consulta = "SELECT * FROM precios where id_producto=:id";
                    $precio = $conn->prepare($consulta);
                    // muestra los productos encontrados
                    while ($pedidos = $resultado->fetch()) {
                        $precio->bindValue(':id', $pedidos['id_producto']);
                        $precio->execute();
                        $productos->execute();
                        echo "
                        <form method='POST'>
                            <tr>
                                <td>" . $pedidos['id_pedido'] . "</td>
                                <td>" . $pedidos['nombre_completo'] . "</td>
                                <td> <select name='id_producto'>";
                        foreach ($productos as $key => $valor) {
                            $selected = ($pedidos['id_producto'] == $valor['id_productos']) ? "selected" : "";
                            echo "<option value='" . $valor['id_productos'] . "' $selected>" . $valor['nombre'] . "</option>";
                        }
                        echo "      </select></td>
                                <td> <select name='precio'>";
                        foreach ($precio as $key => $valor) {
                            $selected = ($pedidos['id_precio'] == $valor['id_precio']) ? "selected" : "";
                            echo "<option value='" . $valor['id_precio'] . "' $selected>" . $valor['tamanio'] . " $" . $valor['precio'] . "</option>";
                        }
                        echo "      </select></td>
                                <td><input type='number' name='cantidad' id='cantidad' value='" . $pedidos['cantidad'] . "' style='width:80px;' required></td>                                
                                <td><input type='text' name='extras' id='extras' value='" . $pedidos['descripcion_extras'] . "'></td>
                                <td>" . date('Y-m-d', strtotime($pedidos['fecha_pedido'])) . "</td>
                                <td>" . $pedidos['estado_pedido'] . "</td>
                                <input type='hidden' name='id_detalle' id='id_detalle' value='" . $pedidos['id_detalle_carrito'] . "'>
                                <td><button class='btn btn-info' type='submit' name='actualizar'>Actualizar</button></td>
                            </tr>
                        </form>";
                    }
                    echo "                                         
                                    </tbody>
                                </table>                                
                            </div>
                        </div>
                    </div>
                </div>";
                } else {
                    echo "<script>alert('No se encontraron pedidos con ese identificador')</script>";
                }
            }
            // Verificar si se ha enviado el formulario de actualizar
            if (isset($_POST['actualizar'])) {
                // Capturar valores del formulario
                $id_detalle_carrito = isset($_POST['id_detalle']) ? (int) $_POST['id_detalle'] : 0;
                $id_producto = isset($_POST['id_producto']) ? $_POST['id_producto'] : 0;
                $id_precio = isset($_POST['precio']) ? $_POST['precio'] : 0;//id_precio
                $cantidad = isset($_POST['cantidad']) ? (int) $_POST['cantidad'] : 0;

                $descripcion_extras = isset($_POST['extras']) ? trim($_POST['extras']) : null;

                // Ejecutar la consulta de busqueda
                $consulta = "SELECT * FROM precios WHERE id_precio=:precio";
                $consulta_precio = $conn->prepare($consulta);
                $consulta_precio->bindValue(':precio', $id_precio);
                $consulta_precio->execute();
                $precioStmt = $consulta_precio->fetch();
                //asignar valores obtenidos de la consulta
                $tamanio = $precioStmt['tamanio'];
                $precio = $precioStmt['precio'];

                // Verificar que se haya recibido un ID válido
                if ($id_detalle_carrito > 0) {
                    // Consulta de actualización con `PDO`
                    $query = "UPDATE detalle_carrito 
                      SET id_producto = :id_producto, 
                          precio = :precio, 
                          cantidad = :cantidad, 
                          tamanio = :tamanio, 
                          descripcion_extras = :descripcion_extras,
                          id_precio = :id_precio
                      WHERE id_detalle_carrito = :id_detalle_carrito";

                    // Preparar la consulta
                    $stmt = $conn->prepare($query);

                    // Bind de parámetros para evitar SQL Injection
                    $stmt->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
                    $stmt->bindParam(':id_precio', $id_precio, PDO::PARAM_STR);
                    $stmt->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
                    $stmt->bindParam(':tamanio', $tamanio, PDO::PARAM_STR);
                    $stmt->bindParam(':precio', $precio, PDO::PARAM_STR);
                    $stmt->bindParam(':descripcion_extras', $descripcion_extras, PDO::PARAM_STR);
                    $stmt->bindParam(':id_detalle_carrito', $id_detalle_carrito, PDO::PARAM_INT);

                    // Ejecutar la consulta
                    if ($stmt->execute()) {
                        echo "<script>alert('Registro actualizado correctamente');</script>";
                    } else {
                        echo "<script>alert('Error al actualizar el registro');</script>";
                    }
                } else {
                    echo "<script>alert('ID de detalle inválido');</script>";
                }
            }
            break;
        case 'eliminar':
            echo "
                <div class='container-fluid'>
                    <h1 class='h3 mb-2 text-gray-800'>Eliminar pedido</h1>                    
                    <div class='card shadow mb-4'>
                        <div class='card-header py-3'>
                            <h6 class='m-0 font-weight-bold text-primary'>Datos</h6>
                        </div>
                        <form method='POST'>
                            <div width='20%' style='margin-left:20px;'>
                                <p class='m-0 font-weight-bold text-dark'>Ingrese el identificador: </p>
                                <input type='number' name='busqueda' id='busqueda'>
                                <button class='btn btn-info' type='submit' name='buscar'>Buscar</button>
                            </div>
                        </form>";
            // Verificar si se ha enviado el formulario
            if (isset($_POST['buscar'])) {
                // Obtener el valor de busqueda
                $nombre = $_POST['busqueda'];

                // Escapar el valor para evitar inyecciones SQL
                $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');

                // Verificar si el parámetro busqueda está definido
                $busqueda = !empty($_POST['busqueda']) ? $_POST['busqueda'] : null;

                //parte fija de la consulta
                $consulta = "SELECT dc.*, p.nombre, p.imagen, ped.id_pedido, ped.estado_pedido, ped.fecha_pedido, 
                        CONCAT(u.nombre, ' ', u.primerap, ' ', u.segundoap) AS nombre_completo 
                    FROM detalle_carrito dc
                    JOIN productos p ON dc.id_producto = p.id_productos
                    JOIN carrito c ON dc.id_carrito = c.id_carrito
                    JOIN pedidos ped ON c.id_carrito = ped.id_carrito
                    JOIN usuarios u ON c.id_usuario = u.id_usuario ";

                // Si está presente, agregamos el WHERE
                if ($busqueda) {
                    $consulta .= " WHERE ped.id_pedido = :pedido ";
                }
                $consulta .= " ORDER BY id_pedido ";
                $resultado = $conn->prepare($consulta);
                // Si está presente, bind el parámetro
                if ($busqueda) {
                    $resultado->bindValue(':pedido', $busqueda);
                }
                $resultado->execute();

                // Mostrar los resultados
                if ($resultado->rowCount() > 0) {
                    echo "
                        <div class='card-body'>
                            <div class='table-responsive'>
                                <table class='table table-hover table-bordered text-dark' id='dataTable' width='90%' cellspacing='0'>
                                    <thead>
                                        <tr>
                                            <th>Pedido</th>
                                            <th>Usuario</th>
                                            <th>Producto</th>
                                            <th>Tamaño</th>
                                            <th>Cantidad</th>
                                            <th>Extras</th>
                                            <th>Fecha</th>
                                            <th>Estado</th>
                                            <th>Opción</th>
                                        </tr>
                                    </thead>
                                    <tbody>";
                    $bandera = true;
                    $pedido_temp = 0;
                    $articulosPorPedido = [];
                    foreach ($resultado->fetchAll(PDO::FETCH_ASSOC) as $row) {
                        $id_pedido = $row['id_pedido']; // Obtener el ID del pedido actual                        
                        // Si el pedido no existe en el array, inicializarlo en 0
                        if (!isset($articulosPorPedido[$id_pedido])) {
                            $articulosPorPedido[$id_pedido] = 0;
                        }
                        // Incrementar el contador de artículos para este pedido
                        $articulosPorPedido[$id_pedido]++;
                    }
                    $resultado->execute();
                    // muestra los productos encontrados
                    while ($pedidos = $resultado->fetch()) {
                        echo "
                        <form method='POST'>
                        <tr>
                            <td>" . $pedidos['id_pedido'] . "</td>
                            <td>" . $pedidos['nombre_completo'] . "</td>
                            <td>" . $pedidos['nombre'] . "</td>
                            <td>" . $pedidos['tamanio'] . " $" . $pedidos['precio'] . "</td>
                            <td>" . $pedidos['cantidad'] . "</td>                                
                            <td>" . $pedidos['descripcion_extras'] . "</td>
                            <td>" . date('Y-m-d', strtotime($pedidos['fecha_pedido'])) . "</td>
                            <td>" . $pedidos['estado_pedido'] . "</td>
                            <input type='hidden' name='id_detalle' id='id_detalle' value='" . $pedidos['id_detalle_carrito'] . "'>
                            <input type='hidden' name='id_pedido' id='id_pedido' value='" . $pedidos['id_pedido'] . "'>
                            <td>";
                        $bandera = ($pedido_temp != $pedidos['id_pedido']);
                        $pedido_temp = $pedidos['id_pedido'];
                        if ($bandera) {
                            echo "<button class='btn btn-danger' type='submit' name='eliminarP' style='width: auto; padding: 5px;'>Eliminar pedido</button>";
                            $bandera = false;
                        }
                        if ($articulosPorPedido[$pedidos['id_pedido']] > 1) {
                            echo "<button class='btn btn-warning' type='submit' name='eliminarD' style='width: auto; padding: 5px;'>Eliminar artículo</button>";
                        }
                        echo "
                            </td>
                        </tr>
                        </form>";
                    }
                    echo "                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>";

                } else {
                    echo "<script>alert('No se encontraron pedidos con ese identificador')</script>";
                }
            }//eliminar pedido
            if (isset($_POST['eliminarP'])) {
                $id_pedido = $_POST['id_pedido'];

                // Escapar el valor para evitar inyecciones SQL
                $id_pedido = htmlspecialchars($id_pedido, ENT_QUOTES, 'UTF-8');
                //eliminar primero el registro en pagos
                $sqlPagos = "DELETE FROM pagos WHERE id_pedido = :id_pedido";
                $stmtPagos = $conn->prepare($sqlPagos);
                $stmtPagos->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
                $stmtPagos->execute();
                // Ejecutar la consulta de busqueda para confirmar el usuario
                $consulta = "DELETE FROM pedidos WHERE id_pedido = :id_pedido";
                $resultado = $conn->prepare($consulta);
                $resultado->bindValue(':id_pedido', $id_pedido);

                // Mostrar los resultados
                if ($resultado->execute()) {
                    echo "<script>alert('Pedido eliminado con exito')</script>";
                }
            }//eliminar detalle carrito
            if (isset($_POST['eliminarD'])) {
                $id_detalle = $_POST['id_detalle'];

                // Escapar el valor para evitar inyecciones SQL
                $id_detalle = htmlspecialchars($id_detalle, ENT_QUOTES, 'UTF-8');
                // Ejecutar la consulta de busqueda para confirmar el usuario
                $consulta = "DELETE FROM detalle_carrito WHERE id_detalle_carrito = :id_detalle";
                $resultado = $conn->prepare($consulta);
                $resultado->bindValue(':id_detalle', $id_detalle);

                // Mostrar los resultados
                if ($resultado->execute()) {
                    echo "<script>alert('Articulo eliminado con exito')</script>";
                }
            }
            break;
        case 'buscar':
            echo "
                <div class='container-fluid'>
                    <h1 class='h3 mb-2 text-gray-800'>Pedidos</h1>                    
                    <div class='card shadow mb-4'>
                        <div class='card-header py-3'>
                            <h6 class='m-0 font-weight-bold text-primary'>Datos</h6>
                        </div>
                        <form method='POST'>
                            <div width='20%' style='margin-left:20px;'>
                                <p class='m-0 font-weight-bold text-dark'>Ingrese el id de pedido: </p>
                                <input type='text' name='busqueda' id='busqueda'>
                                <button class='btn btn-info' type='submit' name='buscar'>Buscar</button>
                            </div>
                        </form>";
            // Verificar si se ha enviado el formulario
            if (isset($_POST['buscar'])) {
                // Obtener el valor de busqueda
                $nombre = $_POST['busqueda'];

                // Escapar el valor para evitar inyecciones SQL
                $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');

                // Verificar si el parámetro busqueda está definido
                $busqueda = !empty($_POST['busqueda']) ? $_POST['busqueda'] : null;

                //parte fija de la consulta
                $consulta = "SELECT dc.*, p.nombre, p.imagen, ped.id_pedido, ped.estado_pedido, ped.fecha_pedido, 
                        CONCAT(u.nombre, ' ', u.primerap, ' ', u.segundoap) AS nombre_completo 
                    FROM detalle_carrito dc
                    JOIN productos p ON dc.id_producto = p.id_productos
                    JOIN carrito c ON dc.id_carrito = c.id_carrito
                    JOIN pedidos ped ON c.id_carrito = ped.id_carrito
                    JOIN usuarios u ON c.id_usuario = u.id_usuario ";

                // Si está presente, agregamos el WHERE
                if ($busqueda) {
                    $consulta .= " WHERE ped.id_pedido = :pedido ";
                }
                $consulta .= " ORDER BY id_pedido ";
                $resultado = $conn->prepare($consulta);
                // Si está presente, bind el parámetro
                if ($busqueda) {
                    $resultado->bindValue(':pedido', $busqueda);
                }
                $resultado->execute();

                // Mostrar los resultados
                if ($resultado->rowCount() > 0) {
                    echo "
                        <div class='card-body'>
                            <div class='table-responsive'>
                                <table class='table table-hover table-bordered text-dark' id='dataTable' width='90%' cellspacing='0'>
                                    <thead>
                                        <tr>
                                            <th>Pedido</th>
                                            <th>Usuario</th>
                                            <th>Producto</th>
                                            <th>Tamaño</th>
                                            <th>Cantidad</th>
                                            <th>Extras</th>
                                            <th>Fecha</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>";
                    // muestra los productos encontrados
                    while ($pedidos = $resultado->fetch()) {
                        echo "
                        <tr>
                            <td>" . $pedidos['id_pedido'] . "</td>
                            <td>" . $pedidos['nombre_completo'] . "</td>
                            <td>" . $pedidos['nombre'] . "</td>
                            <td>" . $pedidos['tamanio'] . " $" . $pedidos['precio'] . "</td>
                            <td>" . $pedidos['cantidad'] . "</td>                                
                            <td>" . $pedidos['descripcion_extras'] . "</td>
                            <td>" . date('Y-m-d', strtotime($pedidos['fecha_pedido'])) . "</td>
                            <td>" . $pedidos['estado_pedido'] . "</td> 
                        </tr>
                    ";
                    }
                    echo "                                         
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>";
                } else {
                    echo "<script>alert('No se encontraron pedidos con ese identificador')</script>";
                }
            }
            break;
    }
}
?>
<script>//script para añadir/eliminar campos de extras y/o tamaños
    function agregarTamanio() {
        let contenedor = document.getElementById("contenedor-tamanios");
        let div = document.createElement("div");
        div.innerHTML = `
                <input class='h5' type="text" name="tamanios[]" placeholder="Tamaño" required>
                <input class='h5' type="number" step="0.01" name="precios[]" placeholder="Precio" required>
                <button class='btn' type="button" onclick="this.parentNode.remove()">❌</button>
            `;
        contenedor.appendChild(div);
    }

    function agregarExtra() {
        let contenedor = document.getElementById("contenedor-extras");
        let div = document.createElement("div");
        div.innerHTML = `
                <input class='h5' type="text" name="nombres_extra[]" placeholder="Nombre del Extra" required>
                <input class='h5' type="number" step="0.01" name="precios_extra[]" placeholder="Precio Extra" required>
                <button class='btn' type="button" onclick="this.parentNode.remove()">❌</button>
            `;
        contenedor.appendChild(div);
    }
</script>
<script>
    function cargarDatosProducto() {
        var idProducto = document.getElementById("id_producto").value;

        if (idProducto) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "obtener_datos_producto.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.getElementById("datos_producto").innerHTML = xhr.responseText;
                }
            };

            xhr.send("id_producto=" + idProducto);
        }
    }


</script>

<!-- /.container-fluid -->
<?php require_once "menu_admin_inf.php" ?>