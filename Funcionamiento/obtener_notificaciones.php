<?php
include("../Funcionamiento/db/conexion.php");

session_start();

if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['roles'])) {
    echo json_encode(["error" => "Usuario no autenticado"]);
    exit();
}

$usuario_id = $_SESSION['id_usuario']; 
$roles = $_SESSION['roles']; 

$query = "";

if (in_array("Alumno", $roles)) {
    $query = "SELECT id, mensaje, tipo, leido FROM notificaciones WHERE usuario_id = ?";
} elseif (in_array("Docente", $roles)) {
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
