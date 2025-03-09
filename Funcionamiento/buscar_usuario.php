<?php
include("../Funcionamiento/db/conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["id"])) {
    $id = mysqli_real_escape_string($conexion, $_GET["id"]);
    $respuesta = [];

    // Busca en la tabla alumnos POR MATRÍCULA
    $query_alumno = "
        SELECT 
            u.id, 
            u.nombre, 
            u.telefono, 
            a.matricula, 
            a.curp, 
            a.domicilio, 
            a.certificado_preparatoria, 
            a.comprobante_pago, 
            u.correo AS usuario, 
            u.contraseña 
        FROM usuarios u
        INNER JOIN alumnos a ON u.id = a.usuario_id
        WHERE a.matricula = '$id'
    ";

    $resultado_alumno = mysqli_query($conexion, $query_alumno);
    
    if ($resultado_alumno && mysqli_num_rows($resultado_alumno) > 0) {
        $respuesta = mysqli_fetch_assoc($resultado_alumno);
        $respuesta["tipo"] = "alumno";

        // Si el usuario no tiene cuenta, generar una basada en el nombre y matrícula
        if (empty($respuesta["usuario"]) || empty($respuesta["contraseña"])) {
            $respuesta["usuario"] = generarUsuario($respuesta["nombre"], $respuesta["matricula"]);
            $respuesta["contraseña"] = generarContraseña();
        }

        echo json_encode($respuesta);
        exit();
    }

    // Busca en la tabla docentes POR MATRÍCULA_DOCENTE
    $query_docente = "
        SELECT 
            u.id, 
            u.nombre, 
            u.telefono, 
            u.correo AS usuario, 
            u.contraseña, 
            d.RFC, 
            d.matricula_docente
        FROM usuarios u
        INNER JOIN docentes d ON u.id = d.usuario_id
        WHERE d.matricula_docente = '$id'
    ";

    $resultado_docente = mysqli_query($conexion, $query_docente);

    if ($resultado_docente && mysqli_num_rows($resultado_docente) > 0) {
        $respuesta = mysqli_fetch_assoc($resultado_docente);
        $respuesta["tipo"] = "docente";

        // Si el usuario no tiene cuenta, generar una basada en el nombre y matrícula_docente
        if (empty($respuesta["usuario"]) || empty($respuesta["contraseña"])) {
            $respuesta["usuario"] = generarUsuario($respuesta["nombre"], $respuesta["matricula_docente"]);
            $respuesta["contraseña"] = generarContraseña();
        }

        echo json_encode($respuesta);
        exit();
    }

    echo json_encode(["error" => "Usuario no encontrado"]);
} else {
    echo json_encode(["error" => "Solicitud inválida"]);
}

// Función para generar un nombre de usuario basado en el nombre y la matrícula
function generarUsuario($nombre, $matricula) {
    $nombre = strtolower(str_replace(" ", ".", trim($nombre))); // Convierte el nombre a minúsculas y reemplaza espacios con puntos
    return $nombre . "." . $matricula . "@red.seysa.mx"; // Combina el nombre, matrícula y la extensión
}

// Función para generar una contraseña segura aleatoria
function generarContraseña() {
    return substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 10);
}