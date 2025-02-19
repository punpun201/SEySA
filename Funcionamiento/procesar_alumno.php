<?php
include("../Funcionamiento/db/conexion.php"); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST["nombre"]);
    $correo = trim($_POST["correo"]);
    $telefono = trim($_POST["telefono"]);
    $matricula = trim($_POST["matricula"]);
    $curp = trim($_POST["curp"]);
    $domicilio = trim($_POST["domicilio"]);
    $carrera_id = trim($_POST["carrera_id"]);
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT); 

    // Insertar en tabla usuarios con consulta preparada
    $query_usuario = "INSERT INTO usuarios (nombre, correo, contraseña, telefono, estado) 
                      VALUES (?, ?, ?, ?, 'Activo')";
    
    if ($stmt = mysqli_prepare($conexion, $query_usuario)) {
        mysqli_stmt_bind_param($stmt, "ssss", $nombre, $correo, $password, $telefono);
        if (mysqli_stmt_execute($stmt)) {
            $usuario_id = mysqli_insert_id($conexion); // Obtiene el ID del usuario recién insertado
            
            // Insertar en tabla alumnos
            $query_alumno = "INSERT INTO alumnos (usuario_id, matricula, curp, domicilio, carrera_id) 
                             VALUES (?, ?, ?, ?, ?)";

            if ($stmt2 = mysqli_prepare($conexion, $query_alumno)) {
                mysqli_stmt_bind_param($stmt2, "isssi", $usuario_id, $matricula, $curp, $domicilio, $carrera_id);
                if (mysqli_stmt_execute($stmt2)) {
                    echo "<script>alert('Alumno registrado exitosamente.'); window.location.href='lista_usuarios.php';</script>";
                } else {
                    echo "<script>alert('Error al registrar el alumno.'); window.history.back();</script>";
                }
                mysqli_stmt_close($stmt2);
            }
        } else {
            echo "<script>alert('Error al registrar el usuario.'); window.history.back();</script>";
        }
        mysqli_stmt_close($stmt);
    }
    
    mysqli_close($conexion);
}
