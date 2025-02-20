<?php
include("Funcionamiento/db/conexion.php");
if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php?error=Debes iniciar sesión.');
    exit();
}

$seccion = isset($_GET['seccion']) ? $_GET['seccion'] : 'inicio';

// Verifica los roles del usuario
$es_admin = in_array("Administrador", $_SESSION['roles']);
$es_docente = in_array("Docente", $_SESSION['roles']);
$es_alumno = in_array("Alumno", $_SESSION['roles']);

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php?error=Debes iniciar sesión.');
    exit();
}

// Consulta para obtener todos los usuarios con su rol
$query = "
    SELECT 
        u.id AS usuario_id, u.nombre, u.correo, u.telefono,
        CASE 
            WHEN a.id IS NOT NULL THEN 'Alumno' 
            WHEN d.id IS NOT NULL THEN 'Docente' 
            ELSE 'Otro' 
        END AS rol,
        a.matricula, c.nombre AS carrera
    FROM usuarios u
    LEFT JOIN alumnos a ON u.id = a.usuario_id
    LEFT JOIN docentes d ON u.id = d.usuario_id
    LEFT JOIN carreras c ON a.carrera_id = c.id
    ORDER BY rol, u.nombre
";
$resultado = mysqli_query($conexion, $query);
