<?php
session_start();
include("../Funcionamiento/db/conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["materia_id"]) && isset($_POST["periodo_id"])) {
    $materia_id = $_POST["materia_id"];
    $periodo_id = $_POST["periodo_id"];

    $sql = "SELECT u.id, u.nombre 
            FROM inscripciones i
            JOIN alumnos a ON i.alumno_id = a.id
            JOIN usuarios u ON a.usuario_id = u.id
            JOIN grupos g ON i.grupo_id = g.id
            WHERE g.materia_id = ? AND g.periodo_id = ?";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $materia_id, $periodo_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $alumnos = array();
    while ($row = $result->fetch_assoc()) {
        $alumnos[] = $row;
    }

    echo json_encode($alumnos);
}
