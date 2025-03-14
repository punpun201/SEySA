<?php
include("../Funcionamiento/db/conexion.php");

// Obtener todas las calificaciones finales registradas con su respectivo alumno
$query = "SELECT 
            c.id AS calificacion_id, 
            i.alumno_id, 
            u.id AS usuario_id, 
            c.calificacion_final, 
            g.id AS grupo_id, 
            m.nombre AS materia
          FROM calificaciones c
          JOIN inscripciones i ON c.inscripcion_id = i.id
          JOIN alumnos a ON i.alumno_id = a.id
          JOIN usuarios u ON a.usuario_id = u.id
          JOIN grupos g ON i.grupo_id = g.id
          JOIN materias m ON g.materia_id = m.id";

$result = mysqli_query($conexion, $query);

if (!$result) {
    die("Error en la consulta: " . mysqli_error($conexion));
}

while ($row = mysqli_fetch_assoc($result)) {
    $calificacion_id = $row['calificacion_id'];
    $usuario_id = $row['usuario_id'];  
    $final = isset($row['calificacion_final']) ? (float)$row['calificacion_final'] : null;
    $materia = $row['materia'];

    // NOTIFICACIÓN SOLO SI NO EXISTE PREVIAMENTE
    $check_query = "SELECT id FROM notificaciones WHERE usuario_id = ? AND calificacion_id = ? AND tipo IN ('felicidades', 'aviso')";
    $stmt_check = mysqli_prepare($conexion, $check_query);
    mysqli_stmt_bind_param($stmt_check, "ii", $usuario_id, $calificacion_id);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);

    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        mysqli_stmt_close($stmt_check);
        continue;  // Evitar duplicaciones
    }
    mysqli_stmt_close($stmt_check);

    // Notificación para el alumno si aprobó o reprobó
    if (!is_null($final) && $final > 0) {
        if ($final >= 6) {
            $mensaje = "¡Felicidades! Has aprobado la materia $materia.";
            $tipo = "felicidades";
        } else {
            $mensaje = "Lo sentimos, has reprobado la materia $materia.";
            $tipo = "aviso";
        }

        // Notificación solo para el alumno
        $query_insert = "INSERT INTO notificaciones (usuario_id, calificacion_id, mensaje, leido, fecha, tipo) 
                         VALUES (?, ?, ?, 0, NOW(), ?)";
        $stmt = mysqli_prepare($conexion, $query_insert);
        mysqli_stmt_bind_param($stmt, "iiss", $usuario_id, $calificacion_id, $mensaje, $tipo);
        mysqli_stmt_execute($stmt);
    }
}

echo json_encode(["success" => true, "message" => "Notificaciones de calificación final generadas correctamente."]);
