<?php
require('../../fpdf186/fpdf.php');
include("../../Funcionamiento/db/conexion.php");

if (!isset($_GET["matricula"]) || !isset($_GET["periodo_id"])) {
    die("Solicitud inválida.");
}

$matricula = $_GET['matricula'];
$periodo_id = $_GET['periodo_id'];

mysqli_set_charset($conexion, "utf8");

// Obtener datos del alumno y sus calificaciones
$query = "SELECT a.matricula, u.nombre AS alumno, p.nombre AS periodo,
                 m.nombre AS materia, c.parcial_1, c.parcial_2, c.parcial_3, c.calificacion_final
          FROM calificaciones c
          JOIN inscripciones i ON c.inscripcion_id = i.id
          JOIN alumnos a ON i.alumno_id = a.id
          JOIN usuarios u ON a.usuario_id = u.id
          JOIN grupos g ON i.grupo_id = g.id
          JOIN materias m ON g.materia_id = m.id
          JOIN periodos p ON g.periodo_id = p.id
          WHERE a.matricula = ? AND p.id = ?";

$stmt = $conexion->prepare($query);
$stmt->bind_param("si", $matricula, $periodo_id);
$stmt->execute();
$result = $stmt->get_result();
$alumno = $result->fetch_assoc();

if (!$alumno) {
    die("No se encontraron datos.");
}

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);

$pdf->SetMargins(15, 10, 15); 
$pdf->SetAutoPageBreak(true, 20); 

// Encabezado 
$pdf->Image('../../Interfaz/img/logo.png', 10, 10, 30); 
$pdf->Ln(30); 

$pdf->SetFillColor(25, 50, 100); 
$pdf->SetTextColor(255); 
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(180, 12, 'SISTEMA DE EVALUACION ACADEMICA', 0, 1, 'C', true);
$pdf->Ln(2); 
$pdf->SetTextColor(0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, 'Direccion: Calle 16, Numero 278, San Francisco de Campeche, Campeche', 0, 1, 'C');
$pdf->Cell(0, 5, 'Tel: +52 123 456 7890 - Email: contacto@red.seysa.mx', 0, 1, 'C');
$pdf->Ln(15);

// Datos del estudiante
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(95, 7, "Estudiante: " . utf8_decode($alumno['alumno']), 0, 0, 'L');
$pdf->Cell(83, 7, "Matricula: " . $alumno['matricula'], 0, 1, 'R');
$pdf->Ln(5);

// Tabla de calificaciones
$pdf->SetFillColor(200, 200, 200);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(55, 10, "Materia", 1, 0, 'C', true);
$pdf->Cell(25, 10, "Parcial 1", 1, 0, 'C', true);
$pdf->Cell(25, 10, "Parcial 2", 1, 0, 'C', true);
$pdf->Cell(25, 10, "Parcial 3", 1, 0, 'C', true);
$pdf->Cell(25, 10, "Final", 1, 0, 'C', true);
$pdf->Cell(25, 10, "Estatus", 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(55, 10, utf8_decode($alumno['materia']), 1, 0, 'C');
$pdf->Cell(25, 10, number_format($alumno['parcial_1'], 0), 1, 0, 'C');
$pdf->Cell(25, 10, number_format($alumno['parcial_2'], 0), 1, 0, 'C');
$pdf->Cell(25, 10, number_format($alumno['parcial_3'], 0), 1, 0, 'C');
$pdf->Cell(25, 10, number_format($alumno['calificacion_final'], 1), 1, 0, 'C');
$pdf->Cell(25, 10, ($alumno['calificacion_final'] >= 6) ? "APROBADO" : "REPROBADO", 1, 1, 'C');

$pdf->Ln(10);

// Pie de página
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetFillColor(25, 50, 100);
$pdf->SetTextColor(255);
$pdf->Cell(180, 10, "Sistema de Evaluacion y Seguimiento Academico", 0, 1, 'C', true);

$pdf->Output('D', 'reporte_' . $alumno['matricula'] . '.pdf');