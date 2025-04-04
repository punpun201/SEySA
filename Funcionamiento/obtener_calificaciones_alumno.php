<?php
session_start();
include("../Funcionamiento/db/conexion.php");

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => 'Sesión no iniciada']);
    exit();
}

$usuario_id = $_SESSION['id_usuario'];
$periodo_id = $_POST['periodo_id'] ?? null;

if (!$periodo_id) {
    echo json_encode(['error' => 'Periodo no especificado']);
    exit();
}

// Obtener el ID del alumno a partir del ID de usuario
$queryAlumno = "SELECT id FROM alumnos WHERE usuario_id = ?";
$stmtAlumno = $conexion->prepare($queryAlumno);
$stmtAlumno->bind_param("i", $usuario_id);
$stmtAlumno->execute();
$resultAlumno = $stmtAlumno->get_result();

if ($resultAlumno->num_rows === 0) {
    echo json_encode(['error' => 'Alumno no encontrado']);
    exit();
}

$alumno = $resultAlumno->fetch_assoc();
$alumno_id = $alumno['id'];

// Obtener inscripciones del alumno en ese período
$query = "
    SELECT i.id AS inscripcion_id, m.nombre AS materia
    FROM inscripciones i
    JOIN grupos g ON g.id = i.grupo_id
    JOIN materias m ON m.id = g.materia_id
    WHERE i.alumno_id = ? AND g.periodo_id = ?
";
$stmt = $conexion->prepare($query);
$stmt->bind_param("ii", $alumno_id, $periodo_id);
$stmt->execute();
$result = $stmt->get_result();

$calificaciones = [];

while ($fila = $result->fetch_assoc()) {
    $inscripcion_id = $fila['inscripcion_id'];
    $materia = $fila['materia'];

    $qCalificacion = "SELECT parcial_1, parcial_2, parcial_3, calificacion_final FROM calificaciones WHERE inscripcion_id = ?";
    $stmtC = $conexion->prepare($qCalificacion);
    $stmtC->bind_param("i", $inscripcion_id);
    $stmtC->execute();
    $resC = $stmtC->get_result();

    if ($resC->num_rows > 0) {
        $calif = $resC->fetch_assoc();
        $calificaciones[] = [
            'materia' => $materia,
            'parcial_1' => $calif['parcial_1'],
            'parcial_2' => $calif['parcial_2'],
            'parcial_3' => $calif['parcial_3'],
            'calificacion_final' => $calif['calificacion_final']
        ];
    } else {
        $calificaciones[] = [
            'materia' => $materia,
            'parcial_1' => null,
            'parcial_2' => null,
            'parcial_3' => null,
            'calificacion_final' => null
        ];
    }
}

echo json_encode($calificaciones);
