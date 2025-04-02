<?php
require('../../fpdf186/fpdf.php');
include("../../Funcionamiento/db/conexion.php");

if (!isset($_GET['periodo_id']) || !isset($_GET['grupo_id'])) {
    die("Solicitud inválida.");
}

$periodo_id = $_GET['periodo_id'];
$grupo_id = $_GET['grupo_id'];
mysqli_set_charset($conexion, 'utf8');

// Obtener información del grupo y periodo
$infoQuery = "SELECT g.nombre AS grupo, p.nombre AS periodo 
              FROM grupos g
              JOIN periodos p ON g.periodo_id = p.id
              WHERE g.id = ?";
$infoStmt = $conexion->prepare($infoQuery);
$infoStmt->bind_param("i", $grupo_id);
$infoStmt->execute();
$infoResult = $infoStmt->get_result()->fetch_assoc();

// Obtener materias asignadas al grupo
$materiasQuery = "SELECT m.id AS materia_id, m.nombre AS materia
                  FROM grupos g
                  JOIN materias m ON g.materia_id = m.id
                  WHERE g.id = ?";
$materiaStmt = $conexion->prepare($materiasQuery);
$materiaStmt->bind_param("i", $grupo_id);
$materiaStmt->execute();
$materias = $materiaStmt->get_result();

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetMargins(10, 10, 10);

// Encabezado
$pdf->Image('../../Interfaz/img/logo.png', 10, 10, 25);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'REPORTE DE RENDIMIENTO GRUPAL', 0, 1, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 6, utf8_decode("Periodo: " . $infoResult['periodo']), 0, 1, 'C');
$pdf->Cell(0, 6, utf8_decode("Grupo: " . $infoResult['grupo']), 0, 1, 'C');
$pdf->Ln(8);

while ($materia = $materias->fetch_assoc()) {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(220, 220, 220);
    $pdf->Cell(0, 8, utf8_decode("Materia: " . $materia['materia']), 0, 1, 'L', true);

    // Tabla encabezado
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(200, 200, 200);
    $pdf->Cell(60, 8, 'Alumno', 1, 0, 'C', true);
    $pdf->Cell(20, 8, 'P1', 1, 0, 'C', true);
    $pdf->Cell(20, 8, 'P2', 1, 0, 'C', true);
    $pdf->Cell(20, 8, 'P3', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Final', 1, 0, 'C', true);
    $pdf->Cell(35, 8, 'Estatus', 1, 1, 'C', true);

    // Alumnos con calificaciones en esta materia
    $alumnosQuery = "SELECT u.nombre AS alumno, 
                            c.parcial_1, c.parcial_2, c.parcial_3, c.calificacion_final
                     FROM calificaciones c
                     JOIN inscripciones i ON c.inscripcion_id = i.id
                     JOIN alumnos a ON i.alumno_id = a.id
                     JOIN usuarios u ON a.usuario_id = u.id
                     JOIN grupos g ON i.grupo_id = g.id
                     WHERE g.id = ? AND g.materia_id = ? AND g.periodo_id = ?";
    $stmt = $conexion->prepare($alumnosQuery);
    $stmt->bind_param("iii", $grupo_id, $materia['materia_id'], $periodo_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $pdf->SetFont('Arial', '', 10);
    if ($result->num_rows === 0) {
        $pdf->Cell(180, 8, 'No hay alumnos inscritos en esta materia.', 1, 1, 'C');
    } else {
        while ($row = $result->fetch_assoc()) {
            $estatus = $row['calificacion_final'] >= 6 ? "APROBADO" : ($row['calificacion_final'] >= 5 ? "RIESGO" : "REPROBADO");
            $pdf->Cell(60, 8, utf8_decode($row['alumno']), 1);
            $pdf->Cell(20, 8, round($row['parcial_1']), 1, 0, 'C');
            $pdf->Cell(20, 8, round($row['parcial_2']), 1, 0, 'C');
            $pdf->Cell(20, 8, round($row['parcial_3']), 1, 0, 'C');
            $pdf->Cell(25, 8, number_format($row['calificacion_final'], 1), 1, 0, 'C');
            $pdf->Cell(35, 8, $estatus, 1, 1, 'C');
        }
    }

    $pdf->Ln(6);
}

$pdf->Output("D", "reporte_grupal.pdf");
?>