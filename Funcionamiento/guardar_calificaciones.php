<?php
header("Content-Type: application/json");
include("../Funcionamiento/db/conexion.php");

if (!$conexion) {
    echo json_encode(["error" => "Fallo en la conexión a la base de datos"]);
    exit();
}

// Recibe datos del POST
$alumno_id = $_POST['alumno_id'] ?? null;
$materia_id = $_POST['materia_id'] ?? null;
$periodo_id = $_POST['periodo_id'] ?? null;
$parcial_1 = $_POST['parcial_1'] ?? null;
$parcial_2 = $_POST['parcial_2'] ?? null;
$parcial_3 = $_POST['parcial_3'] ?? null;

if (!$alumno_id || !$materia_id || !$periodo_id) {
    echo json_encode(["error" => "Datos incompletos"]);
    exit();
}

// Busca el 'inscripcion_id' correcto
$sql = "SELECT i.id FROM inscripciones i
        JOIN grupos g ON i.grupo_id = g.id
        WHERE i.alumno_id = ? AND g.materia_id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $alumno_id, $materia_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $inscripcion_id = $row['id'];
} else {
    echo json_encode(["error" => "El alumno no está inscrito en esta materia"]);
    exit();
}

// Obtiene las calificaciones actuales
$sql = "SELECT parcial_1, parcial_2, parcial_3 FROM calificaciones WHERE inscripcion_id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $inscripcion_id);
$stmt->execute();
$result = $stmt->get_result();
$calificacion_actual = $result->fetch_assoc();

// Conserva los valores anteriores si no se envían nuevos
$parcial_1 = $parcial_1 ?? $calificacion_actual['parcial_1'];
$parcial_2 = $parcial_2 ?? $calificacion_actual['parcial_2'];
$parcial_3 = $parcial_3 ?? $calificacion_actual['parcial_3'];

// Verifica si ya existe una calificación para esta inscripción
$sql = "SELECT id FROM calificaciones WHERE inscripcion_id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $inscripcion_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Actualiza la calificación existente
    $sql = "UPDATE calificaciones 
            SET parcial_1 = ?, parcial_2 = ?, parcial_3 = ?
            WHERE inscripcion_id = ?";
} else {
    // Inserta nueva calificación
    $sql = "INSERT INTO calificaciones (inscripcion_id, parcial_1, parcial_2, parcial_3) 
            VALUES (?, ?, ?, ?)";
}

$stmt = $conexion->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "Error al preparar la consulta de calificación: " . $conexion->error]);
    exit();
}

if ($row) {
    $stmt->bind_param("iiii", $parcial_1, $parcial_2, $parcial_3, $inscripcion_id);
} else {
    $stmt->bind_param("iiii", $inscripcion_id, $parcial_1, $parcial_2, $parcial_3);
}

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Error al guardar calificación: " . $stmt->error]);
}

$stmt->close();
$conexion->close();