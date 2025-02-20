<?php
include("../Funcionamiento/db/conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Asegurar que el formulario tenga enctype="multipart/form-data"
    if (!isset($_FILES["curp"]) || !isset($_FILES["certificado_preparatoria"]) || !isset($_FILES["comprobante_pago"])) {
        die("<script>alert('Error: No se han recibido todos los archivos requeridos.'); window.history.back();</script>");
    }

    // Sanitización de los datos
    $nombre = htmlspecialchars(trim($_POST["nombre"]));
    $correo = filter_var(trim($_POST["correo"]), FILTER_SANITIZE_EMAIL);
    $telefono = trim($_POST["telefono"]);
    $matricula = trim($_POST["matricula"]);
    $domicilio = htmlspecialchars(trim($_POST["domicilio"]));
    $carrera_id = intval($_POST["carrera_id"]); // Convertir a entero
    $password = trim($_POST["password"]);

    // Validaciones con expresiones regulares
    if (!preg_match("/^(?!\s*$)(?=.*[A-Za-zÁÉÍÓÚáéíóúÑñ])[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{4,}$/", $nombre)) {
        $errores['nombre'] = "El nombre solo debe contener letras y espacios (mínimo 4 caracteres).";
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores['correo'] = "Correo electrónico inválido.";
    }
    if (!preg_match("/^\d{8}$/", $matricula)) {
        $errores['matricula'] = "La matrícula debe contener exactamente 8 números.";
    }
    if (!preg_match("/^\d{10}$/", $telefono)) {
        $errores['telefono'] = "El teléfono debe contener exactamente 10 dígitos.";
    }
    if (!preg_match("/^[a-zA-Z0-9]{4,15}$/", $password)) {
        $errores['password'] = "La contraseña debe tener entre 4 y 15 caracteres, sin espacios ni caracteres especiales.";
    }
    if (!preg_match("/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s,.:;]{5,100}$/", $domicilio)) {
        $errores['domicilio'] = "El domicilio solo puede contener letras, números, comas y puntos.";
    }

    // Validar que la carrera seleccionada exista en la base de datos
    $query_carrera = "SELECT id FROM carreras WHERE id = ?";
    if ($stmt_carrera = mysqli_prepare($conexion, $query_carrera)) {
        mysqli_stmt_bind_param($stmt_carrera, "i", $carrera_id);
        mysqli_stmt_execute($stmt_carrera);
        mysqli_stmt_store_result($stmt_carrera);
        if (mysqli_stmt_num_rows($stmt_carrera) == 0) {
            die("<script>alert('Error: La carrera seleccionada no es válida.'); window.history.back();</script>");
        }
        mysqli_stmt_close($stmt_carrera);
    }

    // Validación de archivos (CURP, certificado y comprobante de pago)
    $allowed_file_types = ["image/jpg", "image/jpeg", "image/png", "application/pdf"];
    $max_file_size = 2 * 1024 * 1024; // 2MB

    function validarArchivo($archivo) {
        global $allowed_file_types, $max_file_size;
        if ($archivo['size'] > 0) {
            if (!in_array($archivo['type'], $allowed_file_types)) {
                die("<script>alert('Formato de archivo no permitido. Solo JPG, PNG y PDF.'); window.history.back();</script>");
            }
            if ($archivo['size'] > $max_file_size) {
                die("<script>alert('El archivo es demasiado grande. Máximo 2MB.'); window.history.back();</script>");
            }
        }
    }

    validarArchivo($_FILES["curp"]);
    validarArchivo($_FILES["certificado_preparatoria"]);
    validarArchivo($_FILES["comprobante_pago"]);

    // Verificar si el nombre, correo o matrícula ya existen
    $query_verificar = "SELECT id FROM usuarios WHERE nombre = ? OR correo = ? OR EXISTS (SELECT id FROM alumnos WHERE matricula = ?)";
    if ($stmt_verificar = mysqli_prepare($conexion, $query_verificar)) {
        mysqli_stmt_bind_param($stmt_verificar, "sss", $nombre, $correo, $matricula);
        mysqli_stmt_execute($stmt_verificar);
        mysqli_stmt_store_result($stmt_verificar);

        if (mysqli_stmt_num_rows($stmt_verificar) > 0) {
            die("<script>alert('Error: El nombre, correo o la matrícula ya están registrados.'); window.history.back();</script>");
        }
        mysqli_stmt_close($stmt_verificar);
    }

    // Si hay errores, redirigir al formulario con los errores en la URL
    if (!empty($_SESSION["errores"])) {
        header("Location: ../lista.php");
        exit();
    }

    // Encriptar contraseña
    $password_hashed = password_hash($password, PASSWORD_BCRYPT);

    // Define la ruta de almacenamiento de los archivos
    $ruta_subida = "../uploads/";
    if (!file_exists($ruta_subida)) {
        mkdir($ruta_subida, 0777, true);
    }

    function subirArchivo($archivo, $nombre_campo) {
        global $ruta_subida;
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'pdf'];
        $archivo_nombre = $_FILES[$archivo]["name"];
        $archivo_tmp = $_FILES[$archivo]["tmp_name"];
        $archivo_extension = strtolower(pathinfo($archivo_nombre, PATHINFO_EXTENSION));

        if ($archivo_nombre && in_array($archivo_extension, $extensiones_permitidas)) {
            $nombre_archivo_guardado = uniqid() . "_$nombre_campo." . $archivo_extension;
            $ruta_destino = $ruta_subida . $nombre_archivo_guardado;
            move_uploaded_file($archivo_tmp, $ruta_destino);
            return $ruta_destino;
        }
        return null;
    }

    $curp_archivo = subirArchivo("curp", "curp");
    $certificado_archivo = subirArchivo("certificado_preparatoria", "certificado");
    $comprobante_pago_archivo = subirArchivo("comprobante_pago", "pago");

    // Insertar en la tabla usuarios
    $query_usuario = "INSERT INTO usuarios (nombre, correo, contraseña, telefono, estado) VALUES (?, ?, ?, ?, 'Activo')";
    if ($stmt = mysqli_prepare($conexion, $query_usuario)) {
        mysqli_stmt_bind_param($stmt, "ssss", $nombre, $correo, $password_hashed, $telefono);
        
        if (mysqli_stmt_execute($stmt)) {
            $usuario_id = mysqli_insert_id($conexion);

            // Insertar en la tabla alumnos
            $query_alumno = "INSERT INTO alumnos (usuario_id, matricula, curp, domicilio, carrera_id, certificado_preparatoria, comprobante_pago) 
                             VALUES (?, ?, ?, ?, ?, ?, ?)";

            if ($stmt2 = mysqli_prepare($conexion, $query_alumno)) {
                mysqli_stmt_bind_param($stmt2, "isssiss", $usuario_id, $matricula, $curp_archivo, $domicilio, $carrera_id, $certificado_archivo, $comprobante_pago_archivo);
                
                if (mysqli_stmt_execute($stmt2)) {
                    echo "<script>window.location.href='../lista.php?registro=exito';</script>";
                }
                mysqli_stmt_close($stmt2);
            }
        }
        mysqli_stmt_close($stmt);
    }

    mysqli_close($conexion);
}