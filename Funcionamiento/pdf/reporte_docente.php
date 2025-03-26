<?php
require('../../fpdf186/fpdf.php');
require('../db/conexion.php');

if (!isset($_GET['matricula_docente']) || !isset($_GET['materia_id']) || !isset($_GET['periodo_id'])) {
    die("Solicitud inválida.");
}

$matricula_docente = $_GET['matricula_docente'];
$materia_id = $_GET['materia_id'];
$periodo_id = $_GET['periodo_id'];

mysqli_set_charset($conexion, 'utf8');

$query = "SELECT u.nombre AS alumno, a.matricula, c.parcial_1, c.parcial_2, c.parcial_3, c.calificacion_final, 
                 m.nombre AS materia, p.nombre AS periodo
          FROM calificaciones c
          JOIN inscripciones i ON c.inscripcion_id = i.id
          JOIN alumnos a ON i.alumno_id = a.id
          JOIN usuarios u ON a.usuario_id = u.id
          JOIN grupos g ON c.grupo_id = g.id
          JOIN materias m ON g.materia_id = m.id
          JOIN periodos p ON g.periodo_id = p.id
          JOIN docentes d ON g.docente_id = d.id
          WHERE d.matricula_docente = ? AND m.id = ? AND p.id = ?";

$stmt = $conexion->prepare($query);
$stmt->bind_param("sii", $matricula_docente, $materia_id, $periodo_id);
$stmt->execute();
$result = $stmt->get_result();

$datos = [];
$materia = "";
$periodo = "";

while ($row = $result->fetch_assoc()) {
    $datos[] = $row;
    $materia = $row['materia'];
    $periodo = $row['periodo'];
}

if (count($datos) === 0) {
    die("No se encontró información para el reporte.");
}

// Inicializar PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetMargins(20, 20, 20);

// Logo y encabezado
$pdf->Image('../../Interfaz/img/Logo.png', 10, 10, 30);
$pdf->Ln(30);
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetFillColor(25, 50, 100);
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, utf8_decode("SISTEMA DE EVALUACIÓN ACADÉMICA"), 0, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0);
$pdf->Ln(5);
$pdf->Cell(0, 6, utf8_decode("Dirección: Calle 16, Numero 278, San Francisco de Campeche, Campeche"), 0, 1, 'C');
$pdf->Cell(0, 6, utf8_decode("Tel: +52 123 456 7890 - Email: contacto@red.seysa.mx"), 0, 1, 'C');
$pdf->Ln(10);

// Título
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, utf8_decode('Reporte de Rendimiento Docente'), 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, utf8_decode($periodo), 0, 1, 'C');
$pdf->Ln(5);

// Materia
$pdf->Ln(10);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, utf8_decode("$materia"), 0, 1);
$pdf->Ln(2);

// Tabla de calificaciones
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(220, 220, 220);
$pdf->Cell(50, 10, utf8_decode('Nombre del Alumno'), 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Matricula', 1, 0, 'C', true);
$pdf->Cell(15, 10, 'P1', 1, 0, 'C', true);
$pdf->Cell(15, 10, 'P2', 1, 0, 'C', true);
$pdf->Cell(15, 10, 'P3', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'Final', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Estatus', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);

$aprobados = $riesgo = $reprobados = 0;

foreach ($datos as $alumno) {
    $final = $alumno['calificacion_final'];
    $estatus = '';
    if ($final >= 6) {
        $estatus = 'Aprobado';
        $aprobados++;
    } elseif ($final >= 5) {
        $estatus = 'En Riesgo';
        $riesgo++;
    } else {
        $estatus = 'Reprobado';
        $reprobados++;
    }

    $pdf->Cell(50, 10, utf8_decode($alumno['alumno']), 1);
    $pdf->Cell(30, 10, $alumno['matricula'], 1, 0, 'C');
    $pdf->Cell(15, 10, round($alumno['parcial_1']), 1, 0, 'C');
    $pdf->Cell(15, 10, round($alumno['parcial_2']), 1, 0, 'C');
    $pdf->Cell(15, 10, round($alumno['parcial_3']), 1, 0, 'C');
    $pdf->Cell(20, 10, number_format($final, 2), 1, 0, 'C');
    $pdf->Cell(30, 10, $estatus, 1, 1, 'C');
}

$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(50, 10, utf8_decode("Aprobados: $aprobados"), 0, 0);
$pdf->Cell(50, 10, utf8_decode("En Riesgo: $riesgo"), 0, 0);
$pdf->Cell(50, 10, utf8_decode("Reprobados: $reprobados"), 0, 1);

// Pie de página
$pdf->Ln(15);
$pdf->SetFillColor(25, 50, 100);
$pdf->SetTextColor(255);
$pdf->Cell(0, 10, utf8_decode('Sistema de Evaluación y Seguimiento Académico'), 0, 1, 'C', true);

$pdf->Output('D', 'reporte_rendimiento_docente.pdf');
exit;
