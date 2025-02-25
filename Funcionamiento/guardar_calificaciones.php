<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("../Funcionamiento/db/conexion.php");

if (!$conexion) {
    die(json_encode(["error" => "Fallo en la conexión a la base de datos"]));
}

// Recibir datos del POST
$alumno_id = $_POST['alumno_id'] ?? null;
$materia_id = $_POST['materia_id'] ?? null;
$parcial_1 = $_POST['parcial_1'] ?? null;
$parcial_2 = $_POST['parcial_2'] ?? null;
$parcial_3 = $_POST['parcial_3'] ?? null;
$periodo_id = $_POST['periodo_id'] ?? null;

if (!$alumno_id || !$materia_id || !$periodo_id) {
    echo json_encode(["error" => "Datos incompletos"]);
    exit();
}

$sql = "SELECT i.id, g.id AS grupo_id
        FROM inscripciones i
        JOIN grupos g ON i.grupo_id = g.id
        WHERE i.alumno_id = ? AND g.materia_id = ?";

$stmt = $conexion->prepare($sql);
if (!$stmt) {
    die(json_encode(["error" => "Error en la consulta de inscripciones: " . $conexion->error]));
}

$stmt->bind_param("ii", $alumno_id, $materia_id);
if (!$stmt->execute()) {
    die(json_encode(["error" => "Error en la consulta SQL: " . $stmt->error]));
}
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $inscripcion_id = $row['id'];
    $grupo_id = $row['grupo_id']; // <-- Recuperamos grupo_id
} else {
    echo json_encode(["error" => "El alumno no está inscrito en esta materia"]);
    exit();
}

// Verifica si la calificación ya existe**
$sql = "SELECT id FROM calificaciones WHERE inscripcion_id = ?";
$stmt = $conexion->prepare($sql);
if (!$stmt) {
    die(json_encode(["error" => "Error en la consulta de calificaciones: " . $conexion->error]));
}
$stmt->bind_param("i", $inscripcion_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Actualiza calificación existente
    $sql = "UPDATE calificaciones 
            SET parcial_1 = ?, parcial_2 = ?, parcial_3 = ? 
            WHERE inscripcion_id = ?";
} else {
    // Inserta nueva calificación (añadiendo grupo_id)
    $sql = "INSERT INTO calificaciones (inscripcion_id, grupo_id, parcial_1, parcial_2, parcial_3) 
            VALUES (?, ?, ?, ?, ?)";
}

$stmt = $conexion->prepare($sql);
if (!$stmt) {
    die(json_encode(["error" => "Error al preparar la consulta de calificación: " . $conexion->error]));
}

if ($row) {
    $stmt->bind_param("iiii", $parcial_1, $parcial_2, $parcial_3, $inscripcion_id);
} else {
    $stmt->bind_param("iiiii", $inscripcion_id, $grupo_id, $parcial_1, $parcial_2, $parcial_3);
}

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Error al guardar calificación: " . $stmt->error]);
}

$stmt->close();
$conexion->close();