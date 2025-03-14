<?php
session_start();
include("../Funcionamiento/db/conexion.php"); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $contraseña = mysqli_real_escape_string($conexion, $_POST['contraseña']); 

    // Busca al usuario en la base de datos
    $consulta_usuario = "
        SELECT u.*, GROUP_CONCAT(r.nombre) AS roles 
        FROM usuarios u
        LEFT JOIN usuarios_roles ur ON u.id = ur.usuario_id
        LEFT JOIN roles r ON ur.rol_id = r.id
        WHERE u.correo = '$correo' 
        GROUP BY u.id
    ";
    $resultado_usuario = mysqli_query($conexion, $consulta_usuario);

    if ($resultado_usuario && mysqli_num_rows($resultado_usuario) === 1) {
        $fila_usuario = mysqli_fetch_assoc($resultado_usuario);

        // Verifica si la cuenta está activa
        if ($fila_usuario['estado'] !== 'Activo') {
            header('Location: ../index.php?error=Cuenta inactiva. Contacta al administrador.');
            exit();
        }

        // Verifica la contraseña en texto plano o hasheada
        if ($contraseña === $fila_usuario['contraseña'] || password_verify($contraseña, $fila_usuario['contraseña'])) {
            $_SESSION['id_usuario'] = $fila_usuario['id'];
            $_SESSION['nombre'] = $fila_usuario['nombre'];
            $_SESSION['correo'] = $fila_usuario['correo'];
            $_SESSION['telefono'] = $fila_usuario['telefono'];
            $_SESSION['roles'] = explode(',', $fila_usuario['roles']); 

            $_SESSION['contrasena_sin_hash'] = $contraseña_ingresada;
            
            header('Location: ../view/dashboard.php'); 
            exit();
        } else {
            header('Location: ../index.php?error=Contraseña incorrecta.');
            exit();
        }
    }

    // Si el usuario no existe
    header('Location: ../index.php?error=El usuario no existe.');
    exit();
}