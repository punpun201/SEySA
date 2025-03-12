<?php
include("../Funcionamiento/db/conexion.php");

// Obtener calificaciones SOLO si los tres parciales están completos
$query = "SELECT 
            c.id AS calificacion_id, 
            i.alumno_id, 
            u.id AS usuario_id, 
            c.calificacion_final, 
            c.parcial_1, 
            c.parcial_2, 
            c.parcial_3,
            g.id AS grupo_id, 
            d.usuario_id AS docente_usuario_id
          FROM calificaciones c
          JOIN inscripciones i ON c.inscripcion_id = i.id
          JOIN alumnos a ON i.alumno_id = a.id
          JOIN usuarios u ON a.usuario_id = u.id
          JOIN grupos g ON i.grupo_id = g.id
          JOIN docente_materia dm ON g.materia_id = dm.materia_id
          JOIN docentes d ON dm.docente_id = d.id
          WHERE c.parcial_1 IS NOT NULL 
            AND c.parcial_2 IS NOT NULL 
            AND c.parcial_3 IS NOT NULL"; // Solo si los tres parciales están llenos

$result = mysqli_query($conexion, $query);

if (!$result) {
    die(json_encode(["error" => "Error en la consulta: " . mysqli_error($conexion)]));
}

while ($row = mysqli_fetch_assoc($result)) {
    $calificacion_id = $row['calificacion_id'];
    $usuario_id = $row['usuario_id'];  
    $docente_usuario_id = $row['docente_usuario_id'];  
    $final = isset($row['calificacion_final']) ? (float)$row['calificacion_final'] : null;
    $parcial_1 = isset($row['parcial_1']) ? (float)$row['parcial_1'] : null;
    $parcial_2 = isset($row['parcial_2']) ? (float)$row['parcial_2'] : null;
    $parcial_3 = isset($row['parcial_3']) ? (float)$row['parcial_3'] : null;

    // EVITAR NOTIFICACIONES DUPLICADAS
    $check_query = "SELECT id FROM notificaciones WHERE usuario_id = ? AND calificacion_id = ? AND tipo = ?";
    $stmt_check = mysqli_prepare($conexion, $check_query);

    // Notificación de riesgo SOLO si los dos primeros parciales son menores a 6
    if ($parcial_1 < 6 && $parcial_2 < 6) {
        $tipo = "riesgo";
        mysqli_stmt_bind_param($stmt_check, "iis", $usuario_id, $calificacion_id, $tipo);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) == 0) { // Si no existe, la insertamos
            $mensaje = "Atención: Estás en riesgo de reprobar la materia.";
            $query_insert = "INSERT INTO notificaciones (usuario_id, calificacion_id, mensaje, leido, fecha, tipo) 
                            VALUES (?, ?, ?, 0, NOW(), ?)";
            $stmt = mysqli_prepare($conexion, $query_insert);
            mysqli_stmt_bind_param($stmt, "iiss", $usuario_id, $calificacion_id, $mensaje, $tipo);
            mysqli_stmt_execute($stmt);

            // Notificación para el docente (solo riesgo)
            if ($docente_usuario_id) {
                $mensaje_docente = "Alerta: Un estudiante está en riesgo de reprobar.";
                $query_insert_docente = "INSERT INTO notificaciones (usuario_id, calificacion_id, mensaje, leido, fecha, tipo) 
                                        VALUES (?, ?, ?, 0, NOW(), ?)";
                $stmt_docente = mysqli_prepare($conexion, $query_insert_docente);
                mysqli_stmt_bind_param($stmt_docente, "iiss", $docente_usuario_id, $calificacion_id, $mensaje_docente, $tipo);
                mysqli_stmt_execute($stmt_docente);
            }
        }
    }

    // Notificación de aprobado/reprobado SOLO cuando hay calificación final
    if (!is_null($final)) {
        $tipo = ($final >= 6) ? "felicidades" : "aviso";
        $mensaje = ($final >= 6) ? "¡Felicidades! Has aprobado la materia." : "Lo sentimos, has reprobado la materia.";

        mysqli_stmt_bind_param($stmt_check, "iis", $usuario_id, $calificacion_id, $tipo);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) == 0) { // Evita duplicados
            $query_insert = "INSERT INTO notificaciones (usuario_id, calificacion_id, mensaje, leido, fecha, tipo) 
                            VALUES (?, ?, ?, 0, NOW(), ?)";
            $stmt = mysqli_prepare($conexion, $query_insert);
            mysqli_stmt_bind_param($stmt, "iiss", $usuario_id, $calificacion_id, $mensaje, $tipo);
            mysqli_stmt_execute($stmt);
        }
    }
    mysqli_stmt_close($stmt_check);
}

echo json_encode(["success" => true, "message" => "Notificaciones generadas correctamente."]);
