<?php
include("../Funcionamiento/db/conexion.php"); 
session_start();

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["error" => "Usuario no autenticado"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["periodo_id"])) {
    $alumno_id = $_SESSION['id_usuario'];
    $periodo_id = $_POST["periodo_id"];

    $sql = "SELECT m.nombre AS materia, 
                   IFNULL(c.parcial_1, 'N/A') AS parcial_1, 
                   IFNULL(c.parcial_2, 'N/A') AS parcial_2, 
                   IFNULL(c.parcial_3, 'N/A') AS parcial_3, 
                   IFNULL(c.calificacion_final, 'N/A') AS calificacion_final
            FROM inscripciones i
            JOIN grupos g ON i.grupo_id = g.id
            JOIN materias m ON g.materia_id = m.id
            LEFT JOIN calificaciones c ON c.inscripcion_id = i.id
            WHERE i.alumno_id = ? AND g.periodo_id = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $alumno_id, $periodo_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $calificaciones = [];
    while ($row = $result->fetch_assoc()) {
        $calificaciones[] = $row;
    }

    echo json_encode($calificaciones);
}
