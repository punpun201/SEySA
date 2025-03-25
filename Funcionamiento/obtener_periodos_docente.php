<?php
include("../Funcionamiento/db/conexion.php");

if (!isset($_GET['periodo_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Falta el parámetro periodo_id."]);
    exit;
}

$periodo_id = $_GET['periodo_id'];

// Codificación UTF-8 para evitar problemas con acentos
mysqli_set_charset($conexion, "utf8");

$query = "
    SELECT 
        d.matricula_docente,
        u.nombre,
        m.nombre AS materia,
        m.id AS materia_id
    FROM grupos g
    INNER JOIN docentes d ON g.docente_id = d.id
    INNER JOIN usuarios u ON d.usuario_id = u.id
    INNER JOIN materias m ON g.materia_id = m.id
    WHERE g.periodo_id = ?
    GROUP BY d.matricula_docente, m.id
    ORDER BY u.nombre ASC
";

$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $periodo_id);
$stmt->execute();

$resultado = $stmt->get_result();

$docentes = [];

while ($row = $resultado->fetch_assoc()) {
    $docentes[] = [
        "matricula_docente" => $row['matricula_docente'],
        "nombre" => $row['nombre'],
        "materia" => $row['materia'],
        "materia_id" => $row['materia_id']
    ];
}

header("Content-Type: application/json");
echo json_encode($docentes, JSON_UNESCAPED_UNICODE);