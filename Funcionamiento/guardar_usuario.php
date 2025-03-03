<?php
include("../Funcionamiento/db/conexion.php");

header("Content-Type: application/json");

// Obtener los datos del cuerpo de la solicitud
$data = json_decode(file_get_contents("php://input"), true);

// Validar que se recibieron todos los parámetros
if (!isset($data["tipo"], $data["id"], $data["usuario"], $data["contraseña"])) {
    echo json_encode(["error" => "Solicitud inválida, faltan parámetros."]);
    exit();
}

$tipo = mysqli_real_escape_string($conexion, $data["tipo"]);
$id = mysqli_real_escape_string($conexion, $data["id"]);
$usuario = mysqli_real_escape_string($conexion, $data["usuario"]);
$contraseña = mysqli_real_escape_string($conexion, $data["contraseña"]);

if ($tipo === "alumno") {
    $query = "SELECT u.id, u.correo, u.contraseña FROM usuarios u 
              INNER JOIN alumnos a ON u.id = a.usuario_id 
              WHERE a.matricula = '$id'";
} else if ($tipo === "docente") {
    $query = "SELECT u.id, u.correo, u.contraseña FROM usuarios u 
              INNER JOIN docentes d ON u.id = d.usuario_id 
              WHERE d.matricula_docente = '$id'";
} else {
    echo json_encode(["error" => "Tipo de usuario inválido."]);
    exit();
}

$result = mysqli_query($conexion, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode(["error" => "Usuario no encontrado en la base de datos."]);
    exit();
}

$row = mysqli_fetch_assoc($result);

// Si el usuario ya tiene correo y/o contraseña, no se actualiza y se devuelve error
if (!empty($row["correo"]) && !empty($row["contraseña"])) {
    echo json_encode(["error" => "Este usuario ya tiene credenciales asignadas."]);
    exit();
}

// Insertar usuario y contraseña en la base de datos
$update_query = "UPDATE usuarios SET correo = '$usuario', contraseña = '$contraseña' WHERE id = '{$row['id']}'";

if (mysqli_query($conexion, $update_query)) {
    echo json_encode(["success" => "Usuario registrado correctamente.", "usuario" => $usuario, "contraseña" => $contraseña]);
} else {
    echo json_encode(["error" => "Error al actualizar el usuario: " . mysqli_error($conexion)]);
}
