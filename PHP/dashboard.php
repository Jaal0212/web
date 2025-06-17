<?php
// Inicia la sesión
session_start();
// Verifica si la variable de sesión 'id_usuario' está configurada
if (!isset($_SESSION['id_usuario'])) {
    // Si no está configurada, redirige al login
    header('Location: ../index.php');
    exit(); // Asegúra de que el script se detenga después de redirigir
}
require_once "menu_admin_sup.php";
require "conexion.php";
$anio_actual = date("Y");
//consulta para obtener reporte de ventas anual
$consulta = "SELECT EXTRACT(YEAR FROM fecha_pago) AS anio,
       SUM(monto_total) AS total
        FROM pagos
        WHERE fecha_pago BETWEEN '" . $anio_actual . "-01-01' AND '" . $anio_actual . "-12-31'
        GROUP BY EXTRACT(YEAR FROM fecha_pago)
        ORDER BY anio";
$stmt_reporte_anual = $conn->prepare($consulta);
$stmt_reporte_anual->execute();
$reporte_anual = $stmt_reporte_anual->fetch();

//consulta para obtener reporte de ventas mensual

$consulta = "SELECT TO_CHAR(DATE '" . $anio_actual . "-01-01' + INTERVAL '1 month' * EXTRACT(MONTH FROM fecha_pago) - INTERVAL '1 month', 'FMMonth') AS mes,
       SUM(monto_total) AS total
        FROM pagos
        WHERE fecha_pago BETWEEN '" . $anio_actual . "-01-01' AND '" . $anio_actual . "-12-31'
        GROUP BY EXTRACT(MONTH FROM fecha_pago)
        ORDER BY EXTRACT(MONTH FROM fecha_pago);";
$stmt_reporte_mes = $conn->prepare($consulta);
$stmt_reporte_mes->execute();
$reporte_mes = $stmt_reporte_mes->fetchall();
?>

<!-- Contenido -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"
            id="generateReportBtn"><i class="fas fa-download fa-sm text-white-50"></i> Generar Reporte</a>
    </div>

    <!-- Content Row -->
    <div class="row">

        <!-- Earnings (Monthly) Card -->
        <?php
        foreach ($reporte_mes as $key => $value) {
            echo "
                <div class='col-xl-3 col-md-6 mb-4'>
                    <div class='card border-left-primary shadow h-100 py-2'>
                        <div class='card-body'>
                            <div class='row no-gutters align-items-center'>
                                <div class='col mr-2'>
                                    <div class='text-xs font-weight-bold text-primary text-uppercase mb-1'>
                                        Report " . $value['mes'] . "</div>
                                    <div class='h5 mb-0 font-weight-bold text-gray-800'>$" . $value['total'] . "</div>
                                </div>
                                <div class='col-auto'>
                                    <i class='fas fa-calendar fa-2x text-gray-300'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>";
        }//ciclo foreach
        ?>
    </div>
    <!-- End Content Row -->
    <!-- Content Row -->
    <div class="row">
        <!-- Earnings (Anualy) Card -->
        <?php

        echo "
                <div class='col-xl-3 col-md-6 mb-4'>
                    <div class='card border-left-success shadow h-100 py-2'>
                        <div class='card-body'>
                            <div class='row no-gutters align-items-center'>
                                <div class='col mr-2'>
                                    <div class='text-xs font-weight-bold text-success text-uppercase mb-1'>
                                        Report " . $reporte_anual['anio'] . "</div>
                                    <div class='h5 mb-0 font-weight-bold text-gray-800'>$" . $reporte_anual['total'] . "</div>
                                </div>
                                <div class='col-auto'>
                                    <i class='fas fa-dollar-sign fa-2x text-gray-300'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>";
        ?>
    </div>
    <!-- End Content Row -->
</div>

<!-- Escuchar el clic del botón para generar reporte anual -->
<script>
    document.getElementById('generateReportBtn').addEventListener('click', function (e) {
        e.preventDefault();  // Prevenir el enlace de seguir su acción por defecto

        // Solicita al usuario el año
        var year = prompt("Por favor ingrese el año para el reporte:");

        // Validación del año ingresado
        if (year === null || year.trim() === "") {
            alert("El año no puede estar vacío.");
            return; // Salir si no se ingresa un valor
        }

        // Verifica que el año sea un número
        var yearNumber = parseInt(year, 10);
        if (isNaN(yearNumber)) {
            alert("Por favor ingresa un número válido para el año.");
            return;
        }

        // Verifica que el año esté dentro de un rango válido (por ejemplo, no mayor que el año actual)
        var currentYear = new Date().getFullYear();
        if (yearNumber < 2000 || yearNumber > currentYear) {
            alert("Por favor ingresa un año válido (por ejemplo, entre 2000 y el año actual).");
            return;
        }
        // Si el usuario ingresa un valor y no cancela
        if (year && !isNaN(year)) {
            // Aquí puedes redirigir a una URL que genere el reporte PDF para ese año
            window.location.href = 'generarPDF.php?reporte=' + year;            
        } else {
            alert("Por favor ingresa un año válido.");
        }
    });
</script>
<!-- /.container-fluid -->
<?php require_once "menu_admin_inf.php" ?>