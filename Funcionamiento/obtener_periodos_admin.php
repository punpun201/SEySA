<?php
include("../Funcionamiento/db/conexion.php");

// Consulta para obtener todos los periodos
$query = "SELECT DISTINCT p.id, p.nombre 
          FROM periodos p
          INNER JOIN grupos g ON g.periodo_id = p.id
          ORDER BY p.fecha_inicio DESC";

$resultado = mysqli_query($conexion, $query);

$periodos = [];

while ($fila = mysqli_fetch_assoc($resultado)) {
    $periodos[] = $fila;
}

echo json_encode($periodos, JSON_UNESCAPED_UNICODE);
