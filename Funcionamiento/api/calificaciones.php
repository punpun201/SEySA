<?php
require('../db/conexion.php');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

if (!isset($_GET['matricula'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Matrícula no proporcionada']);
    exit;
}

$matricula = $_GET['matricula'];

mysqli_set_charset($conexion, 'utf8');

$query = "
    SELECT 
        m.nombre AS materia,
        p.nombre AS periodo,
        c.parcial_1,
        c.parcial_2,
        c.parcial_3,
        c.calificacion_final
    FROM alumnos a
    INNER JOIN inscripciones i ON a.id = i.alumno_id
    INNER JOIN grupos g ON i.grupo_id = g.id
    INNER JOIN materias m ON g.materia_id = m.id
    INNER JOIN periodos p ON g.periodo_id = p.id
    INNER JOIN calificaciones c ON c.inscripcion_id = i.id
    WHERE a.matricula = ?
";

$stmt = $conexion->prepare($query);
$stmt->bind_param('s', $matricula);
$stmt->execute();
$resultado = $stmt->get_result();

$calificaciones = [];

while ($fila = $resultado->fetch_assoc()) {
    $fila['calificacion_final'] = number_format($fila['calificacion_final'], 2);
    $calificaciones[] = $fila;
}

if (empty($calificaciones)) {
    http_response_code(404);
    echo json_encode(['mensaje' => 'No se encontraron calificaciones para la matrícula proporcionada.']);
} else {
    echo json_encode(['matricula' => $matricula, 'calificaciones' => $calificaciones]);
}