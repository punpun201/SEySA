<?php
header("Content-Type: application/json");
include("../Funcionamiento/db/conexion.php");

if (!$conexion) {
    echo json_encode(["error" => "Fallo en la conexión a la base de datos"]);
    exit();
}

$materia_id = $_POST['materia_id'] ?? null;
$periodo_id = $_POST['periodo_id'] ?? null;

if (!$materia_id || !$periodo_id) {
    echo json_encode(["error" => "Datos incompletos"]);
    exit();
}

$sql = "
    SELECT u.nombre AS nombre, a.id AS alumno_id, 
           c.parcial_1, c.parcial_2, c.parcial_3, 
           c.calificacion_final
    FROM inscripciones i
    JOIN alumnos a ON i.alumno_id = a.id
    JOIN usuarios u ON a.usuario_id = u.id
    JOIN grupos g ON i.grupo_id = g.id
    LEFT JOIN calificaciones c ON i.id = c.inscripcion_id
    WHERE g.materia_id = ? AND g.periodo_id = ?
    ORDER BY u.nombre
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $materia_id, $periodo_id);
$stmt->execute();
$result = $stmt->get_result();

$alumnos = [];
while ($row = $result->fetch_assoc()) {
    $alumnos[] = [
        "id" => $row["alumno_id"],
        "nombre" => $row["nombre"],
        "parcial_1" => $row["parcial_1"] ?? "",
        "parcial_2" => $row["parcial_2"] ?? "",
        "parcial_3" => $row["parcial_3"] ?? "",
        "calificacion_final" => $row["calificacion_final"] ?? "Pendiente"
    ];
}

echo json_encode($alumnos);
$stmt->close();
$conexion->close();
