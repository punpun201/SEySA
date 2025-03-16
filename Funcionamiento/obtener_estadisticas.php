<?php
include("../Funcionamiento/db/conexion.php");
session_start();

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["error" => "Usuario no autenticado"]);
    exit();
}

$periodo_id = isset($_POST['periodo_id']) ? intval($_POST['periodo_id']) : 0;
$materia_id = isset($_POST['materia_id']) ? intval($_POST['materia_id']) : 0;

if ($periodo_id == 0 || $materia_id == 0) {
    echo json_encode(["error" => "Parámetros inválidos"]);
    exit();
}

// Obtener el número total de alumnos inscritos en la materia y periodo seleccionados

$query = "SELECT COUNT(*) AS inscritos FROM inscripciones i
          JOIN grupos g ON i.grupo_id = g.id
          WHERE g.periodo_id = ? AND g.materia_id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("ii", $periodo_id, $materia_id);
$stmt->execute();
$resultado = $stmt->get_result();
$inscritos = $resultado->fetch_assoc()['inscritos'];

// Contar alumnos aprobados, en riesgo y reprobados, y calcular el promedio general
$query = "SELECT
            SUM(CASE WHEN CAST(c.calificacion_final AS FLOAT) >= 6 THEN 1 ELSE 0 END) AS aprobados,
            SUM(CASE WHEN CAST(c.calificacion_final AS FLOAT) >= 4 AND CAST(c.calificacion_final AS FLOAT) < 6 THEN 1 ELSE 0 END) AS en_riesgo,
            SUM(CASE WHEN CAST(c.calificacion_final AS FLOAT) < 6 THEN 1 ELSE 0 END) AS reprobados,
            AVG(CAST(c.calificacion_final AS FLOAT)) AS promedio
          FROM calificaciones c
          JOIN inscripciones i ON c.inscripcion_id = i.id
          JOIN grupos g ON i.grupo_id = g.id
          WHERE g.periodo_id = ? AND g.materia_id = ?";

$stmt = $conexion->prepare($query);
$stmt->bind_param("ii", $periodo_id, $materia_id);
$stmt->execute();
$resultado = $stmt->get_result();
$datos = $resultado->fetch_assoc();

// Obtener promedios por parcial

$query = "SELECT AVG(CAST(c.parcial_1 AS FLOAT)) AS p1, AVG(CAST(c.parcial_2 AS FLOAT)) AS p2, AVG(CAST(c.parcial_3 AS FLOAT)) AS p3
          FROM calificaciones c
          JOIN inscripciones i ON c.inscripcion_id = i.id
          JOIN grupos g ON i.grupo_id = g.id
          WHERE g.periodo_id = ? AND g.materia_id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("ii", $periodo_id, $materia_id);
$stmt->execute();
$resultado = $stmt->get_result();
$parciales = $resultado->fetch_assoc();

// Formatear datos y enviarlos en formato JSON
$response = [
    "inscritos" => $inscritos,
    "aprobados" => $datos['aprobados'] ?? 0,
    "en_riesgo" => $datos['en_riesgo'] ?? 0,
    "reprobados" => $datos['reprobados'] ?? 0,
    "promedio" => number_format($datos['promedio'] ?? 0, 2),
    "promedios_parciales" => [
        number_format($parciales['p1'] ?? 0, 2),
        number_format($parciales['p2'] ?? 0, 2),
        number_format($parciales['p3'] ?? 0, 2)
    ]
];

echo json_encode($response);
