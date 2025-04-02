<?php
ini_set('display_errors', 0);
error_reporting(0);
include("../Funcionamiento/db/conexion.php");
header('Content-Type: application/json');

if (!isset($_GET['periodo_id'])) {
    echo json_encode([]);
    exit;
}

$periodo_id = $_GET['periodo_id'];
mysqli_set_charset($conexion, "utf8");

$query = "SELECT id, nombre FROM grupos WHERE periodo_id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $periodo_id);
$stmt->execute();

$resultado = $stmt->get_result();
$grupos = [];

while ($row = $resultado->fetch_assoc()) {
    $grupos[] = $row;
}

echo json_encode($grupos);