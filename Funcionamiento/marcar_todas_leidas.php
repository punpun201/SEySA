<?php
include("../Funcionamiento/db/conexion.php");

session_start();
if (!isset($_SESSION['id_usuario'])) {
    exit();
}

$usuario_id = $_SESSION['id_usuario'];

$query = "UPDATE notificaciones SET leido = 1 WHERE usuario_id = ?";
$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, "i", $usuario_id);
mysqli_stmt_execute($stmt);
