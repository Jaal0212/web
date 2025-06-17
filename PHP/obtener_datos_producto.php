<?php
require 'conexion.php';

if (isset($_POST['id_producto'])) {
    $id_producto = $_POST['id_producto'];

    // Consulta para obtener los precios del producto
    $sqlPrecios = "SELECT id_precio, tamanio, precio FROM precios WHERE id_producto = :id_producto";
    $stmtPrecios = $conn->prepare($sqlPrecios);
    $stmtPrecios->execute(['id_producto' => $id_producto]);
    $precios = $stmtPrecios->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para obtener los extras del producto
    $sqlExtras = "SELECT id_extra, nombre_extra, precio_extra FROM extras WHERE id_producto = :id_producto";
    $stmtExtras = $conn->prepare($sqlExtras);
    $stmtExtras->execute(['id_producto' => $id_producto]);
    $extras = $stmtExtras->fetchAll(PDO::FETCH_ASSOC);

    echo "<form method='POST' action='tables.php?productos=editar'>"; // Formulario para editar datos
    echo "<input type='hidden' name='id_producto' value='{$id_producto}'>";

    // **Mostrar tabla de precios**
    if ($precios) {
        echo "<h4 class='font-weight-bold text-dark'>Precios</h4>";
        echo "<table class='table'><tr><th>Tamaño</th><th>Precio</th><th>Acción</th></tr>";
        foreach ($precios as $precio) {
            echo "<tr>
                    <td><input type='text' name='tamanios[{$precio['id_precio']}]' value='{$precio['tamanio']}'></td>
                    <td><input type='number' name='precios[{$precio['id_precio']}]' step='0.01' value='{$precio['precio']}'></td>
                    <td><a href='obtener_datos_producto.php?idP={$precio['id_precio']}' class='btn'>❌</a></td>
                </tr>";
        }
        echo "</table>";
    }

    // **Mostrar tabla de extras**
    if ($extras) {
        echo "<h4 class='font-weight-bold text-dark'>Extras</h4>";
        echo "<table class='table'><tr><th>Nombre</th><th>Precio</th><th>Acción</th></tr>";
        foreach ($extras as $extra) {
            echo "<tr>
                    <td><input type='text' name='nombres_extra[{$extra['id_extra']}]' value='{$extra['nombre_extra']}'></td>
                    <td><input type='number' name='precios_extra[{$extra['id_extra']}]' step='0.01' value='{$extra['precio_extra']}'></td>
                    <td><a href='obtener_datos_producto.php?idE={$extra['id_extra']}' class='btn'>❌</a></td>
                </tr>";
        }
        echo "</table>";
    }

    echo "<button class='btn btn-success' name='guardarEdicion' type='submit'>Guardar Cambios</button>";
    echo "</form>";
}

// **Eliminar extras**
if (isset($_GET['idE'])) {
    $sql = "DELETE FROM extras WHERE id_extra = :id_extra";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id_extra' => $_GET['idE']]);
    echo "<script>alert('Extra eliminado correctamente'); window.location.href='tables.php?productos=editar';</script>";
    exit();
}

// **Eliminar precios**
if (isset($_GET['idP'])) {
    $sql = "DELETE FROM precios WHERE id_precio = :id_precio";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id_precio' => $_GET['idP']]);
    echo "<script>alert('Precio eliminado correctamente'); window.location.href='tables.php?productos=editar';</script>";
    exit();
}
?>