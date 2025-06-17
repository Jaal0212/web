<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Productos</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <!-- Stylesheet -->
    <link rel="stylesheet" href="../assets/css/Style.css">
    <link rel="stylesheet" href="../assets/css/StyleProduct.css">

</head>

<body data-spy="scroll" data-target=".navbar" data-offset="40" id="home">
    <!-- Navbar -->
    <?php
    // Inicia la sesión
    session_start();
    require_once "menu.php";

    // Ejecutar la consulta para obtener las categorias padre
    $consultaCategorias = "SELECT * FROM categoria WHERE id_categoria_padre IS NULL;";
    $resultado = $conn->prepare($consultaCategorias);
    $resultado->execute();
    ?>
    <div class="contenedorPrincipal">
        <!-- Shop Start -->
        <div class="Categoria">
            <h5 class="title">Categorias</h5>
            <ul class="menu_principal">
                <?php
                // Mostrar los resultados
                if ($resultado->rowCount() > 0) {
                    while ($categoria = $resultado->fetch()) {
                        echo " <li><a href='Productos.php?categoria=" . $categoria['id_categoria'] . "'>" . $categoria['nombre_categoria'] . "</a>";
                        
                        // Ejecutar la consulta para obtener las categorias hijo
                        $consultaSubCategorias = "SELECT * FROM categoria WHERE id_categoria_padre=" . $categoria['id_categoria'] . ";";
                        $stmt = $conn->prepare($consultaSubCategorias);
                        $stmt->execute();
                        echo "<ul class='menu'>";
                        if ($stmt->rowCount() > 0) {
                            while ($subcategoria = $stmt->fetch()) {
                                echo " <li><a href='Productos.php?categoria=" . $subcategoria['id_categoria'] . "'>" . $subcategoria['nombre_categoria'] . "</a>";
                            }
                        }
                        echo "</ul> 
                        </li>";
                    }                    
                    
                }
                ?>                
            </ul>
        </div>
        <!-- Categoria End -->
        <?php
        //modifica los productos que se muestran en base a la categoria seleccionada
        //si no se selecciono ninguna hace la consulta por defecto, mostrar todo
        if (isset($_GET['categoria']) || isset($_GET['subcategoria'])) {
            $id_categoria = $_GET['categoria'];
            $consultaProductos = "SELECT DISTINCT ON (p.id_productos) p.*, pr.*
                                    FROM productos p
                                    JOIN precios pr ON p.id_productos = pr.id_producto 
                                    where id_categoria= $id_categoria";
        } else {
            $consultaProductos = "SELECT DISTINCT ON (p.id_productos) p.*, pr.*
                                    FROM productos p
                                    JOIN precios pr ON p.id_productos = pr.id_producto";
        }
        // Ejecutar la consulta para obtener los productos        
        $resultado = $conn->prepare($consultaProductos);
        $resultado->execute();
        ?>
        <!-- Productos-->
        <div class="articulos">
            <?php

            // Mostrar los resultados
            if ($resultado->rowCount() > 0) {
                while ($producto = $resultado->fetch()) {
                    echo "
                        <div class='item'>
                            <figure>
                                <a href='Detalle.php?producto=" . $producto['id_productos'] . "'><img src='" . $producto['imagen'] . "' alt='producto' /></a>
                            </figure>
                            <div class='info-product'>
                                <h2>" . $producto['nombre'] . "</h2>
                                <p class='price'>$" . $producto['precio'] . "</p>
                                <button ><a href='Detalle.php?producto=" . $producto['id_productos'] . "' style='color:white;'>Añadir al carrito</a></button>
                            </div>
                        </div>
                        ";
                }
            }
            ?>
        </div>
    </div>
    <!-- Shop End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary back-to-top"><i class="fa fa-angle-double-up"></i></a>


    <!-- core  -->
    <script src="../assets/vendors/jquery/jquery-3.4.1.js"></script>
    <script src="../assets/vendors/bootstrap/bootstrap.bundle.js"></script>

    <!-- bootstrap affix -->
    <script src="../assets/vendors/bootstrap/bootstrap.affix.js"></script>

    <script src="../assets/js/Productos.js"></script>
</body>

</html>