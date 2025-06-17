<?php
require "conexion.php";
require('../assets/lib/FPDF/fpdf.php'); // librería FPDF
if (isset($_GET['reporte'])) {
    ob_start();  // Inicia el búfer de salida
    // Obtener el año desde el parámetro 'anio' en la URL
    $anio = isset($_GET['reporte']) ? intval($_GET['reporte']) : date('Y');  // Si no se pasa, tomar el año actual
    try {    
        // Consultar los datos filtrando por el año
        $sql = "SELECT TO_CHAR(DATE '" . $anio . "-01-01' + INTERVAL '1 month' * EXTRACT(MONTH FROM fecha_pago) - INTERVAL '1 month', 'FMMonth') AS mes,
        SUM(monto_total) AS total
         FROM pagos
         WHERE fecha_pago BETWEEN '" . $anio . "-01-01' AND '" . $anio . "-12-31'
         GROUP BY EXTRACT(MONTH FROM fecha_pago)
         ORDER BY EXTRACT(MONTH FROM fecha_pago);";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    
        // Obtener los resultados
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($resultados)) {
            echo "<script>alert('No se encontraron datos para el año $anio');
            window.location.href = 'dashboard.php';</script>";
            return;
        }
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }

    // Crear una instancia de la clase FPDF
    $pdf = new FPDF();
    $pdf->AddPage();
    // Establecer el título del PDF en los metadatos
    $pdf->SetTitle('Reporte de Pagos 2025');

    // Título del reporte
    $pdf->SetFont('Helvetica', 'B', 16);
    $pdf->Cell(200, 10, "Reporte de Pagos - $anio", 0, 1, 'C');

    // Encabezado de las columnas
    // Formato del encabezado de la tabla
    $pdf->SetFont('Arial', 'B', 12);  // Fuente en negrita
    $pdf->SetFillColor(200, 220, 255);  // Color de fondo del encabezado (color RGB)
    $pdf->SetTextColor(0, 0, 0);  // Color del texto (negro)
    $pdf->Cell(50, 10, 'Mes', 1, 0, 'C', true);  // Celda con borde y relleno
    $pdf->Cell(40, 10, 'Monto Total', 1, 1, 'C', true);  // Celda con borde y relleno

    // Restablecer el formato para los datos de la tabla
    $pdf->SetFont('Arial', '', 12);  // Fuente normal
        
    // Recorre los resultados y agregarlos al PDF
    foreach ($resultados as $row) {        
        $pdf->Cell(50, 10, $row['mes'], 1);
        $pdf->Cell(40, 10, number_format($row['total'], 2), 1);
        $pdf->Ln();
    }
    // Salida del PDF (esto generará el archivo PDF y lo enviará al navegador) 
    $pdf->Output('I', "reporte_anual_".$anio.".pdf");
    exit;  // Asegura que el código se detenga después de generar el PDF         
}
ob_end_clean();  // Limpia y desactiva el búfer de salida
?>