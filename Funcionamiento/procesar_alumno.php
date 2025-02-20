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
    $regex_nombre = "/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,50}$/";
    $regex_correo = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
    $regex_matricula = "/^\d{8}$/";
    $regex_telefono = "/^\d{10}$/";
    $regex_password = "/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/";
    $regex_domicilio = "/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ,. ]{5,100}$/";

    if (!preg_match($regex_nombre, $nombre)) {
        die("<script>alert('El nombre solo debe contener letras y espacios (mínimo 3 caracteres).'); window.history.back();</script>");
    }
    if (!preg_match($regex_correo, $correo)) {
        die("<script>alert('Correo electrónico inválido.'); window.history.back();</script>");
    }
    if (!preg_match($regex_matricula, $matricula)) {
        die("<script>alert('La matrícula debe contener exactamente 8 números.'); window.history.back();</script>");
    }
    if (!preg_match($regex_telefono, $telefono)) {
        die("<script>alert('El teléfono debe contener exactamente 10 dígitos.'); window.history.back();</script>");
    }
    if (!preg_match($regex_password, $password)) {
        die("<script>alert('La contraseña debe tener al menos 8 caracteres, incluir letras y números.'); window.history.back();</script>");
    }
    if (!preg_match($regex_domicilio, $domicilio)) {
        die("<script>alert('El domicilio solo puede contener letras, números, comas y puntos.'); window.history.back();</script>");
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

    // Encriptar contraseña
    $password_hashed = password_hash($password, PASSWORD_BCRYPT);

    // Definir la ruta de almacenamiento de los archivos
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
