<?php
require('../../fpdf186/fpdf.php');
include("../../Funcionamiento/db/conexion.php");

if (!isset($_GET["matricula"]) || !isset($_GET["materia_id"]) || !isset($_GET["periodo_id"])) {
    die("Solicitud inválida.");
}

$matricula = $_GET['matricula'];
$materia_id = $_GET['materia_id'];
$periodo_id = $_GET['periodo_id'];

mysqli_set_charset($conexion, "utf8");

// Obtener datos del alumno
$query = "SELECT a.matricula, u.nombre AS alumno, m.nombre AS materia, p.nombre AS periodo, 
          c.parcial_1, c.parcial_2, c.parcial_3, c.calificacion_final
          FROM calificaciones c
          JOIN inscripciones i ON c.inscripcion_id = i.id
          JOIN alumnos a ON i.alumno_id = a.id
          JOIN usuarios u ON a.usuario_id = u.id
          JOIN grupos g ON i.grupo_id = g.id
          JOIN materias m ON g.materia_id = m.id
          JOIN periodos p ON g.periodo_id = p.id
          WHERE a.matricula = ? AND m.id = ? AND p.id = ?";

$stmt = $conexion->prepare($query);
$stmt->bind_param("sii", $matricula, $materia_id, $periodo_id);
$stmt->execute();
$result = $stmt->get_result();
$alumno = $result->fetch_assoc();

if (!$alumno) {
    die("No se encontraron registros.");
}

// Calcular si aprobó o reprobó
$estatus = ($alumno['calificacion_final'] >= 6) ? "APROBADO" : "REPROBADO";

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(190, 10, 'Reporte de Calificaciones', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(190, 8, 'Periodo: ' . $alumno['periodo'], 0, 1, 'C');

// Espacio antes de la tabla
$pdf->Ln(5);

// Información del alumno
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(95, 10, "Estudiante: " . utf8_decode($alumno['alumno']), 0, 0, 'L');
$pdf->Cell(95, 10, "Matricula: " . $alumno['matricula'], 0, 1, 'R');

$pdf->Ln(5);

// Crear tabla
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(60, 10, 'Materia', 1, 0, 'C');
$pdf->Cell(25, 10, 'Parcial 1', 1, 0, 'C');
$pdf->Cell(25, 10, 'Parcial 2', 1, 0, 'C');
$pdf->Cell(25, 10, 'Parcial 3', 1, 0, 'C');
$pdf->Cell(25, 10, 'Final', 1, 0, 'C');
$pdf->Cell(30, 10, 'Estatus', 1, 1, 'C');

// Datos de la tabla
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(60, 10, utf8_decode($alumno['materia']), 1, 0, 'C');
$pdf->Cell(25, 10, number_format($alumno['parcial_1'], 2), 1, 0, 'C');
$pdf->Cell(25, 10, number_format($alumno['parcial_2'], 2), 1, 0, 'C');
$pdf->Cell(25, 10, number_format($alumno['parcial_3'], 2), 1, 0, 'C');
$pdf->Cell(25, 10, number_format($alumno['calificacion_final'], 2), 1, 0, 'C');
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(30, 10, $estatus, 1, 1, 'C');

$pdf->Output('D', 'reporte_' . $matricula . '.pdf');
exit();