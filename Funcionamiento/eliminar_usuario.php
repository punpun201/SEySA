<?php
include("Funcionamiento/db/conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_usuario'])) {
    $id_usuario = intval($_POST['id_usuario']);

    // Cambiar estado a "Inactivo"
    $query = "UPDATE usuarios SET estado = 'Inactivo' WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_usuario);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["success" => true, "message" => "Usuario dado de baja exitosamente."]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al cambiar el estado del usuario."]);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conexion);
}
