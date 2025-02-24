<?php
include("../Funcionamiento/db/conexion.php"); 
session_start();

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["error" => "Usuario no autenticado"]);
    exit();
}

$usuario_id = $_SESSION['id_usuario']; // El usuario logueado
$periodo_id = isset($_POST['periodo_id']) ? intval($_POST['periodo_id']) : 0;

if ($periodo_id == 0) {
    echo json_encode(["error" => "Periodo no seleccionado"]);
    exit();
}

// Consulta SQL corregida
$query = "SELECT DISTINCT m.id, m.nombre 
          FROM grupos g
          JOIN materias m ON g.materia_id = m.id
          JOIN docentes d ON g.docente_id = d.id
          JOIN usuarios u ON d.usuario_id = u.id
          WHERE u.id = ? AND g.periodo_id = ?";

$stmt = $conexion->prepare($query);
$stmt->bind_param("ii", $usuario_id, $periodo_id);
$stmt->execute();
$resultado = $stmt->get_result();

$materias = [];
while ($fila = $resultado->fetch_assoc()) {
    $materias[] = $fila;
}

echo json_encode($materias);
