<?php
require('../../fpdf186/fpdf.php');  
include("../../Funcionamiento/db/conexion.php");

if (!isset($_GET["tipo"]) || !isset($_GET["id"])) {
    die("Solicitud inválida.");
}

$tipo = $_GET["tipo"];
$id = $_GET["id"];

// Configurar MySQL para que devuelva los datos con codificación correcta
mysqli_set_charset($conexion, "utf8");

// Consultar los datos del usuario en la base de datos
if ($tipo === "alumno") {
    $query = "SELECT u.nombre, u.telefono, u.correo AS usuario, u.contraseña, 
                     a.matricula, a.curp, a.domicilio, a.certificado_preparatoria, a.comprobante_pago
              FROM usuarios u
              INNER JOIN alumnos a ON u.id = a.usuario_id
              WHERE a.matricula = '$id'";
} elseif ($tipo === "docente") {
    $query = "SELECT u.nombre, u.telefono, u.correo AS usuario, u.contraseña, 
                     d.matricula_docente, d.RFC
              FROM usuarios u
              INNER JOIN docentes d ON u.id = d.usuario_id
              WHERE d.matricula_docente = '$id'";
} else {
    die("Tipo de usuario inválido.");
}

$resultado = mysqli_query($conexion, $query);

if (!$resultado || mysqli_num_rows($resultado) === 0) {
    die("Usuario no encontrado.");
}

$datos = mysqli_fetch_assoc($resultado);

// Crear una nueva instancia de FPDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont("Arial", "B", 14);
$pdf->Cell(0, 10, utf8_decode("RECIBO DE DOCUMENTO"), 0, 1, "C"); // Título centrado
$pdf->Ln(10);

// Configuración de interlineado similar a Word
$pdf->SetFont("Arial", "", 11);
$pdf->MultiCell(0, 6, utf8_decode(
    "El " . ucfirst($tipo) . " " . $datos["nombre"] . ", de matrícula " . 
    ($tipo === "alumno" ? $datos["matricula"] : $datos["matricula_docente"]) . 
    ", confirma haber recibido este documento que avala la creación de su cuenta para acceder al sistema.\n\n"
    . "En caso de no recibir este documento en el momento de su creación, tiene como un plazo de 10 días hábiles "
    . "para confirmar el recibimiento de este documento que avala su acceso al sistema."
), 0, "L"); // Alineado a la izquierda
$pdf->Ln(8);

// Datos de usuario y contraseña alineados a la izquierda con etiquetas en negrita
$pdf->SetFont("Arial", "B", 11);
$pdf->Cell(50, 6, utf8_decode("Usuario:"), 0, 1, "L");
$pdf->SetFont("Arial", "", 11);
$pdf->Cell(50, 6, utf8_decode($datos["usuario"]), 0, 1, "L");

$pdf->Ln(4);

$pdf->SetFont("Arial", "B", 11);
$pdf->Cell(50, 6, utf8_decode("Contraseña:"), 0, 1, "L");
$pdf->SetFont("Arial", "", 11);
$pdf->Cell(50, 6, utf8_decode($datos["contraseña"]), 0, 1, "L");

$pdf->Ln(20);

// Sección de firma centrada
$pdf->SetFont("Arial", "B", 11);
$pdf->Cell(0, 8, utf8_decode("Recibe"), 0, 1, "C");
$pdf->Ln(10);
$pdf->Cell(0, 8, "________________________________", 0, 1, "C");
$pdf->Cell(0, 8, "__________________", 0, 1, "C");

// Generar el archivo PDF
$pdf->Output("D", "Credenciales_$id.pdf"); // Fuerza la descarga del archivo
