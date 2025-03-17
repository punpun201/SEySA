<?php
include("../Funcionamiento/db/conexion.php");
session_start();

if (!isset($_POST['materia_id']) || !isset($_POST['periodo_id'])) {
    echo json_encode([]);
    exit();
}

$materia_id = intval($_POST['materia_id']);
$periodo_id = intval($_POST['periodo_id']);

$query = "SELECT a.matricula, u.nombre 
          FROM inscripciones i
          JOIN alumnos a ON i.alumno_id = a.id
          JOIN usuarios u ON a.usuario_id = u.id
          JOIN grupos g ON i.grupo_id = g.id
          WHERE g.materia_id = ? AND g.periodo_id = ?";

$stmt = $conexion->prepare($query);
if (!$stmt) {
    echo json_encode(["error" => "Error en la consulta SQL", "detalle" => $conexion->error]);
    exit();
}

$stmt->bind_param("ii", $materia_id, $periodo_id);
$stmt->execute();
$resultado = $stmt->get_result();

$alumnos = [];
while ($fila = $resultado->fetch_assoc()) {
    $alumnos[] = $fila;
}

header("Content-Type: application/json");
echo json_encode($alumnos);
