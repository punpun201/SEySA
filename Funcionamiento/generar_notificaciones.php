<?php
include("../Funcionamiento/db/conexion.php");

$query = "SELECT 
            c.id AS calificacion_id, 
            i.alumno_id, 
            u.id AS usuario_id, 
            c.calificacion_final, 
            c.parcial_1, 
            c.parcial_2, 
            c.parcial_3, 
            g.id AS grupo_id, 
            d.usuario_id AS docente_usuario_id,
            m.nombre AS materia_nombre, 
            u.nombre AS alumno_nombre
          FROM calificaciones c
          JOIN inscripciones i ON c.inscripcion_id = i.id
          JOIN alumnos a ON i.alumno_id = a.id
          JOIN usuarios u ON a.usuario_id = u.id
          JOIN grupos g ON i.grupo_id = g.id
          JOIN materias m ON g.materia_id = m.id
          JOIN docente_materia dm ON g.materia_id = dm.materia_id
          JOIN docentes d ON dm.docente_id = d.id";

$result = mysqli_query($conexion, $query);

if (!$result) {
    die("Error en la consulta: " . mysqli_error($conexion));
}

while ($row = mysqli_fetch_assoc($result)) {
    $calificacion_id = $row['calificacion_id'];
    $usuario_id = $row['usuario_id'];  
    $docente_usuario_id = $row['docente_usuario_id'];  
    $materia_nombre = $row['materia_nombre'];
    $alumno_nombre = $row['alumno_nombre'];
    $final = isset($row['calificacion_final']) ? (float)$row['calificacion_final'] : null;
    $parcial_1 = isset($row['parcial_1']) ? (float)$row['parcial_1'] : null;
    $parcial_2 = isset($row['parcial_2']) ? (float)$row['parcial_2'] : null;
    $parcial_3 = isset($row['parcial_3']) ? (float)$row['parcial_3'] : null;

    // Verificar si la notificación ya existe para evitar duplicados
    $check_query = "SELECT id FROM notificaciones WHERE usuario_id = ? AND calificacion_id = ?";
    $stmt_check = mysqli_prepare($conexion, $check_query);
    mysqli_stmt_bind_param($stmt_check, "ii", $usuario_id, $calificacion_id);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);

    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        mysqli_stmt_close($stmt_check);
        continue;
    }
    mysqli_stmt_close($stmt_check);

    // Notificación de riesgo antes del tercer parcial
    if (!is_null($parcial_1) && !is_null($parcial_2) && $parcial_1 < 6 && $parcial_2 < 6) {
        $mensaje = "Atención: Estás en riesgo de reprobar la materia $materia_nombre.";
        $tipo = "riesgo";

        // Notificación para el alumno
        $query_insert = "INSERT INTO notificaciones (usuario_id, calificacion_id, mensaje, leido, fecha, tipo) 
                         VALUES (?, ?, ?, 0, NOW(), ?)";
        $stmt = mysqli_prepare($conexion, $query_insert);
        mysqli_stmt_bind_param($stmt, "iiss", $usuario_id, $calificacion_id, $mensaje, $tipo);
        mysqli_stmt_execute($stmt);

        // Notificación para el docente con el nombre del alumno en riesgo
        if ($docente_usuario_id) {
            $mensaje_docente = "Alerta: El estudiante $alumno_nombre está en riesgo de reprobar la materia $materia_nombre.";
            $query_insert_docente = "INSERT INTO notificaciones (usuario_id, calificacion_id, mensaje, leido, fecha, tipo) 
                                     VALUES (?, ?, ?, 0, NOW(), ?)";
            $stmt_docente = mysqli_prepare($conexion, $query_insert_docente);
            mysqli_stmt_bind_param($stmt_docente, "iiss", $docente_usuario_id, $calificacion_id, $mensaje_docente, $tipo);
            mysqli_stmt_execute($stmt_docente);
        }
    }

    // Notificación cuando se tiene la calificación final
    if (!is_null($final) && $final > 0) {
        if ($final >= 6) {
            $mensaje = "¡Felicidades! Has aprobado la materia $materia_nombre.";
            $tipo = "felicidades";
        } else {
            $mensaje = "Lo sentimos, has reprobado la materia $materia_nombre.";
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

echo json_encode(["success" => true, "message" => "Notificaciones generadas correctamente."]);
