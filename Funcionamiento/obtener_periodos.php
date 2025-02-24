<?php
include("../Funcionamiento/db/conexion.php");

$query = "SELECT id, nombre FROM periodos ORDER BY fecha_inicio DESC";
$result = mysqli_query($conexion, $query);

$periodos = [];
while ($row = mysqli_fetch_assoc($result)) {
    $periodos[] = $row;
}

echo json_encode($periodos);
