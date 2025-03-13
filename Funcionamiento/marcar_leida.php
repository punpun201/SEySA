<?php
include("../Funcionamiento/db/conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'];

    $query = "UPDATE notificaciones SET leido = 1 WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
}
