<?php
include("../Funcionamiento/db/conexion.php");
session_start();

header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($_SESSION["id_usuario"])) {
    echo json_encode(["error" => "Sesión no iniciada."]);
    exit();
}

if (!isset($data["contrasena"])) {
    echo json_encode(["error" => "Falta la nueva contraseña."]);
    exit();
}

$nuevaContrasena = $data["contrasena"];
$idUsuario = $_SESSION["id_usuario"];

// Validar formato de la contraseña
if (!preg_match("/^[a-zA-Z0-9]{4,}$/", $nuevaContrasena)) {
    echo json_encode(["error" => "La contraseña debe tener mínimo 4 caracteres, solo letras y números."]);
    exit();
}

// Verificar si la contraseña ya existe en otro usuario
$queryGlobalCheck = "SELECT id FROM usuarios WHERE contraseña = ?";
$stmtCheckGlobal = $conexion->prepare($queryGlobalCheck);
$hashNuevaContrasena = password_hash($nuevaContrasena, PASSWORD_BCRYPT);
$stmtCheckGlobal->bind_param("s", $hashNuevaContrasena);
$stmtCheckGlobal->execute();
$stmtCheckGlobal->store_result();

if ($stmtCheckGlobal->num_rows > 0) {
    echo json_encode(["error" => "Esta contraseña ya está en uso por otro usuario."]);
    exit();
}

// Guardar contraseña en sesión antes de hashearla
$_SESSION["ultima_contrasena"] = $nuevaContrasena;

// Hashear la nueva contraseña y actualizar en la base de datos
$queryUpdate = "UPDATE usuarios SET contraseña = ? WHERE id = ?";
$stmtUpdate = $conexion->prepare($queryUpdate);
$stmtUpdate->bind_param("si", $hashNuevaContrasena, $idUsuario);

if ($stmtUpdate->execute()) {
    echo json_encode(["success" => "Contraseña actualizada correctamente."]);
} else {
    echo json_encode(["error" => "Error al actualizar la contraseña."]);
}