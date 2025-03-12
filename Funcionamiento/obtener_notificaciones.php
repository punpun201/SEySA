<?php
include("../Funcionamiento/db/conexion.php");

session_start();

if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['rol'])) {
    echo json_encode(["error" => "Usuario no autenticado"]);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$rol = $_SESSION['rol']; 

$query = "";

if ($rol == "alumno") {
    $query = "SELECT id, mensaje, tipo, leido FROM notificaciones WHERE usuario_id = ?";
} elseif ($rol == "docente") {
    $query = "SELECT id, mensaje, tipo, leido FROM notificaciones WHERE usuario_id = ? AND tipo = 'riesgo'";
} else {
    echo json_encode(["error" => "Rol no válido"]);
    exit();
}

$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, "i", $usuario_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$notificaciones = [];
while ($row = mysqli_fetch_assoc($result)) {
    $notificaciones[] = $row;
}

echo json_encode($notificaciones);