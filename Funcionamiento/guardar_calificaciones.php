<?php
header("Content-Type: application/json");
include("../Funcionamiento/db/conexion.php");

if (!$conexion) {
    echo json_encode(["error" => "Fallo en la conexión a la base de datos"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$materia_id = $data['materia_id'] ?? null;
$periodo_id = $data['periodo_id'] ?? null;
$calificaciones = $data['calificaciones'] ?? [];

if (!$materia_id || !$periodo_id || empty($calificaciones)) {
    echo json_encode(["error" => "Datos incompletos"]);
    exit();
}

foreach ($calificaciones as $calificacion) {
    $alumno_id = $calificacion['alumno_id'];
    $parcial_1 = $calificacion['parcial_1'] ?? null;
    $parcial_2 = $calificacion['parcial_2'] ?? null;
    $parcial_3 = $calificacion['parcial_3'] ?? null;

    // Obtiene el inscripcion_id
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
        continue;
    }

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
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("iiii", $parcial_1, $parcial_2, $parcial_3, $inscripcion_id);
    } else {
        // Inserta una nueva calificación
        $sql = "INSERT INTO calificaciones (inscripcion_id, parcial_1, parcial_2, parcial_3) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("iiii", $inscripcion_id, $parcial_1, $parcial_2, $parcial_3);
    }

    $stmt->execute();
}

echo json_encode(["success" => true]);

$conexion->close();