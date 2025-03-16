<?php
include("../Funcionamiento/db/conexion.php"); 
session_start();

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["error" => "Usuario no autenticado"]);
    exit();
}

$periodo_id = isset($_POST['periodo_id']) ? intval($_POST['periodo_id']) : 0;

if ($periodo_id == 0) {
    echo json_encode(["error" => "Periodo no seleccionado"]);
    exit();
}

$query = "SELECT DISTINCT m.id, m.nombre 
          FROM grupos g
          JOIN materias m ON g.materia_id = m.id
          WHERE g.periodo_id = ?";

$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $periodo_id);
$stmt->execute();
$resultado = $stmt->get_result();

$materias = [];
while ($fila = $resultado->fetch_assoc()) {
    $materias[] = $fila;
}

echo json_encode($materias);

