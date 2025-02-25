<?php
header("Content-Type: application/json");
include("../Funcionamiento/db/conexion.php");

if (!$conexion) {
    echo json_encode(["error" => "Fallo en la conexión a la base de datos"]);
    exit();
}

// Recibe los datos del POST
$materia_id = $_POST['materia_id'] ?? null;
$periodo_id = $_POST['periodo_id'] ?? null;

if (!$materia_id || !$periodo_id) {
    echo json_encode(["error" => "Datos incompletos"]);
    exit();
}

// Consulta para obtener los alumnos y sus calificaciones de la materia y período seleccionados
$sql = "
    SELECT a.id AS alumno_id, a.nombre AS nombre, 
           c.parcial_1, c.parcial_2, c.parcial_3, 
           c.calificacion_final
    FROM inscripciones i
    JOIN alumnos a ON i.alumno_id = a.id
    JOIN grupos g ON i.grupo_id = g.id
    LEFT JOIN calificaciones c ON i.id = c.inscripcion_id
    WHERE g.materia_id = ? AND g.periodo_id = ?
    ORDER BY a.nombre
";

$stmt = $conexion->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "Error en la consulta: " . $conexion->error]);
    exit();
}

$stmt->bind_param("ii", $materia_id, $periodo_id);
$stmt->execute();
$result = $stmt->get_result();

$alumnos = [];
while ($row = $result->fetch_assoc()) {
    $alumnos[] = [
        "id" => $row["alumno_id"],
        "nombre" => $row["nombre"],
        "parcial_1" => $row["parcial_1"] ?? null,
        "parcial_2" => $row["parcial_2"] ?? null,
        "parcial_3" => $row["parcial_3"] ?? null,
        "calificacion_final" => $row["calificacion_final"] ?? "Pendiente"
    ];
}

echo json_encode($alumnos);

$stmt->close();
$conexion->close();
