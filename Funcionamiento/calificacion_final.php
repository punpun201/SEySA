<?php
include("../Funcionamiento/db/conexion.php");

$alumno_id = $_POST['alumno_id'] ?? null;
$materia_id = $_POST['materia_id'] ?? null;
$periodo_id = $_POST['periodo_id'] ?? null;

if (!$alumno_id || !$materia_id || !$periodo_id) {
    echo json_encode(["error" => "Datos incompletos"]);
    exit();
}

// Obtiene el ID de inscripción correctamente asegurando el periodo**
$sql = "SELECT i.id 
        FROM inscripciones i
        JOIN grupos g ON i.grupo_id = g.id
        WHERE i.alumno_id = ? AND g.materia_id = ? AND g.periodo_id = ?
        LIMIT 1";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("iii", $alumno_id, $materia_id, $periodo_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $inscripcion_id = $row['id'];
} else {
    echo json_encode(["error" => "No se encontró la inscripción del alumno en la materia y período seleccionados"]);
    exit();
}
$stmt->close();

// Obtiene los parciales asegurando que no sean NULL**
$sql = "SELECT 
            COALESCE(parcial_1, 0) AS parcial_1, 
            COALESCE(parcial_2, 0) AS parcial_2, 
            COALESCE(parcial_3, 0) AS parcial_3 
        FROM calificaciones 
        WHERE inscripcion_id = ?
        LIMIT 1";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $inscripcion_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $parcial_1 = $row['parcial_1'];
    $parcial_2 = $row['parcial_2'];
    $parcial_3 = $row['parcial_3'];

    // Calcula la calificación final
    $calificacion_final = ($parcial_1 * 0.30) + ($parcial_2 * 0.30) + ($parcial_3 * 0.40);
    $calificacion_final = round($calificacion_final, 2); 

    // Actualiza la calificación final en la BD
    $update_sql = "UPDATE calificaciones SET calificacion_final = ? WHERE inscripcion_id = ?";
    $update_stmt = $conexion->prepare($update_sql);
    $update_stmt->bind_param("di", $calificacion_final, $inscripcion_id);

    if ($update_stmt->execute()) {
        echo json_encode(["success" => true, "calificacion_final" => $calificacion_final]);
    } else {
        echo json_encode(["error" => "Error al actualizar la calificación final"]);
    }

    $update_stmt->close();
} else {
    echo json_encode(["error" => "No se encontraron calificaciones para el alumno"]);
}

$stmt->close();
$conexion->close();
