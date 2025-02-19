<?php
session_start();  

include(__DIR__ . "/db/conexion.php");  

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php?error=Debes iniciar sesión.');
    exit();
}

// Obtener ID del usuario logueado
$id_usuario = $_SESSION['id_usuario'];

$nombre = !empty($usuario['nombre']) ? $usuario['nombre'] : "No disponible";
$telefono = !empty($usuario['telefono']) ? $usuario['telefono'] : "No disponible";
$correo = !empty($usuario['correo']) ? $usuario['correo'] : "No disponible";
$contraseña = !empty($usuario['contraseña']) ? "********" : "No disponible";
$matricula = ($es_alumno && !empty($usuario['matricula'])) ? $usuario['matricula'] : "No disponible";
$id_carrera = ($es_alumno && !empty($usuario['id_carrera'])) ? $usuario['id_carrera'] : "No disponible";

// Verificar si $_SESSION['roles'] está definida
$roles_usuario = $_SESSION['roles'] ?? [];

$es_admin = in_array("Administrador", $roles_usuario);
$es_docente = in_array("Docente", $roles_usuario);
$es_alumno = in_array("Alumno", $roles_usuario);

// Inicializa la variable para almacenar los datos del usuario
$usuario = [];

// Si es Administrador o Docente, obtiene los datos básicos
if ($es_admin || $es_docente) {
    $query = "SELECT nombre, correo, telefono, contraseña FROM usuarios WHERE id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();
}

// Si es Alumno, obtiene datos adicionales como matrícula e ID de carrera
if ($es_alumno) {
    $query_alumno = "
        SELECT u.nombre, u.correo, u.telefono, u.contraseña, a.matricula, a.carrera_id
        FROM usuarios u 
        JOIN alumnos a ON u.id = a.usuario_id 
        WHERE u.id = ?";
    $stmt = $conexion->prepare($query_alumno);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $resultado_alumno = $stmt->get_result();
    $usuario = $resultado_alumno->fetch_assoc();
}
