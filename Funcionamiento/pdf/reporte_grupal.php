<?php
require('../../fpdf186/fpdf.php');
require('../db/conexion.php');

if (!isset($_GET['grupo_id']) || !isset($_GET['periodo_id'])) {
    die("Solicitud inválida.");
}

$grupo_id = $_GET['grupo_id'];
$periodo_id = $_GET['periodo_id'];

mysqli_set_charset($conexion, 'utf8');

// Consulta para obtener datos del grupo, periodo y alumnos con sus materias y calificaciones
$query = "SELECT g.nombre AS grupo, p.nombre AS periodo, 
                 u.nombre AS alumno, a.matricula,
                 m.nombre AS materia, 
                 c.parcial_1, c.parcial_2, c.parcial_3, c.calificacion_final
          FROM calificaciones c
          INNER JOIN inscripciones i ON c.inscripcion_id = i.id
          INNER JOIN alumnos a ON i.alumno_id = a.id
          INNER JOIN usuarios u ON a.usuario_id = u.id
          INNER JOIN grupos g ON i.grupo_id = g.id
          INNER JOIN materias m ON g.materia_id = m.id
          INNER JOIN periodos p ON g.periodo_id = p.id
          WHERE g.id = ? AND p.id = ?
          ORDER BY u.nombre, m.nombre";

$stmt = $conexion->prepare($query);
$stmt->bind_param('ii', $grupo_id, $periodo_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("No se encontraron datos para el grupo y periodo seleccionados.");
}

// Inicializar PDF
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);

// Encabezado
$pdf->Image('../../Interfaz/img/Logo.png', 10, 10, 30);
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('SISTEMA DE EVALUACIÓN ACADÉMICA'), 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, utf8_decode('Direccion: Calle 16, Numero 278, San Francisco de Campeche, Campeche'), 0, 1, 'C');
$pdf->Cell(0, 5, utf8_decode('Tel: +52 123 456 7890 - Email: contacto@red.seysa.mx'), 0, 1, 'C');
$pdf->Ln(8);

// Datos generales del reporte
$row = $resultado->fetch_assoc(); 
$periodo = $row['periodo'];
$grupo = $row['grupo'];
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, utf8_decode("Reporte de Rendimiento Académico"), 0, 1, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 6, utf8_decode($periodo), 0, 1, 'C');
$pdf->Cell(0, 6, utf8_decode("Grupo: ") . utf8_decode($grupo), 0, 1, 'C');
$pdf->Ln(5);

// Regresar el puntero al inicio para procesar todos los datos
$resultado->data_seek(0);

// Nombre de la materia (como encabezado extra arriba de la tabla)
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 8, utf8_decode('Materia: ' . $row['materia']), 0, 1, 'C');
$pdf->Ln(3);

// Encabezado de tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(220, 220, 220);
$pdf->SetX(50); 
$pdf->Cell(50, 10, utf8_decode('Alumno'), 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Matricula', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'P1', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'P2', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'P3', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Final', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Estatus', 1, 1, 'C', true);

// Cuerpo de la tabla
$pdf->SetFont('Arial', '', 9);
while ($row = $resultado->fetch_assoc()) {
    $estatus = $row['calificacion_final'] >= 6 ? 'APROBADO' : 'REPROBADO';

    $pdf->SetX(50); 
    $pdf->Cell(50, 8, utf8_decode($row['alumno']), 1);
    $pdf->Cell(30, 8, $row['matricula'], 1);
    $pdf->Cell(20, 8, intval($row['parcial_1']), 1, 0, 'C');
    $pdf->Cell(20, 8, intval($row['parcial_2']), 1, 0, 'C');
    $pdf->Cell(20, 8, intval($row['parcial_3']), 1, 0, 'C');
    $pdf->Cell(25, 8, number_format($row['calificacion_final'],), 1, 0, 'C');

    // Estatus con color
    if ($estatus === 'APROBADO') {
        $pdf->SetTextColor(0, 128, 0); // verde
    } else {
        $pdf->SetTextColor(220, 20, 60); // rojo
    }
    $pdf->Cell(30, 8, $estatus, 1, 1, 'C');
    $pdf->SetTextColor(0); // reset color
}

// Final
$pdf->Ln(15);
$pdf->SetFont('Arial', 'I', 9);
$pdf->Cell(0, 6, utf8_decode('Este documento es confidencial y exclusivo para fines académicos.'), 0, 1, 'C');

// Salida del PDF
$pdf->Output('I', 'reporte_grupal.pdf');
exit;
