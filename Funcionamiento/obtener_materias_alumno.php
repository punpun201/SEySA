<?php
session_start();
include("../Funcionamiento/db/conexion.php");

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["error" => "Usuario no autenticado."]);
    exit();
}

$id_alumno = $_SESSION['id_usuario'];

if (!isset($_POST['periodo_id'])) {
    echo json_encode(["error" => "No se recibió el período."]);
    exit();
}

$periodo_id = intval($_POST['periodo_id']);

// Consulta para obtener las materias en las que el alumno está inscrito en ese período
$sql = "
    SELECT m.id, m.nombre 
    FROM inscripciones i
    JOIN grupos g ON i.grupo_id = g.id
    JOIN materias m ON g.materia_id = m.id
    WHERE i.alumno_id = ? AND g.periodo_id = ?
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $id_alumno, $periodo_id);
$stmt->execute();
$result = $stmt->get_result();

$materias = [];
while ($row = $result->fetch_assoc()) {
    $materias[] = $row;
}

// Devuelve las materias en formato JSON
echo json_encode($materias);

$stmt->close();
$conexion->close();
