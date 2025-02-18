<?php

$server = "localhost";
$user = "root";
$pass = "";
$db = "seysa";

$conexion = new mysqli ($server, $user, $pass, $db);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
} else {
    echo "Conexión exitosa a la base de datos!";
}
?>