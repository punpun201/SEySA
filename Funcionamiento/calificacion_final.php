<?php
include("../Funcionamiento/db/conexion.php");

// Obtener todas las calificaciones donde los tres parciales tienen valores
$query = "SELECT id, parcial_1, parcial_2, parcial_3 FROM calificaciones 
          WHERE parcial_1 IS NOT NULL AND parcial_2 IS NOT NULL AND parcial_3 IS NOT NULL";
$result = mysqli_query($conexion, $query);

if (!$result) {
    die("Error en la consulta: " . mysqli_error($conexion));
}

while ($row = mysqli_fetch_assoc($result)) {
    $calificacion_id = $row['id'];
    $parcial_1 = (float)$row['parcial_1'];
    $parcial_2 = (float)$row['parcial_2'];
    $parcial_3 = (float)$row['parcial_3'];

    // Calcular la calificación final solo si los tres parciales tienen valores
    $calificacion_final = round(($parcial_1 + $parcial_2 + $parcial_3) / 3, 2);

    // Actualizar la calificación final en la base de datos
    $update_query = "UPDATE calificaciones SET calificacion_final = ? WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $update_query);
    mysqli_stmt_bind_param($stmt, "di", $calificacion_final, $calificacion_id);
    mysqli_stmt_execute($stmt);
}

mysqli_close($conexion);

echo "Cálculo de calificaciones finales actualizado correctamente.";
