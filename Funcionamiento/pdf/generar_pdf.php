<?php
require('../../fpdf186/fpdf.php');
require('../db/conexion.php');

if (!isset($_GET['tipo']) || !isset($_GET['id'])) {
    die("Solicitud inválida.");
}

$tipo = $_GET['tipo'];
$id = $_GET['id'];

// Consultar los datos del usuario
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

$nombre = $datos['nombre'];
$matricula = $tipo === "alumno" ? $datos['matricula'] : $datos['matricula_docente'];
$usuario = $datos['usuario'];
$contrasena = $datos['contraseña'];

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true);
$pdf->SetMargins(20, 20, 20);

// Logo
$pdf->Image('../../Interfaz/img/logo.png', 0, -1, 25);
$pdf->Ln(30);

// Encabezado
$pdf->SetY(20);
$pdf->SetFillColor(25, 50, 100);
$pdf->SetTextColor(255);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, utf8_decode('RECIBO DE CREACIÓN DE CUENTA'), 0, 1, 'C', true);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(60);
$pdf->Cell(0, 8, utf8_decode('Documento de confirmación de credenciales'), 0, 1, 'C');
$pdf->Ln(15);

// Cuerpo del texto
$pdf->SetTextColor(0);
$pdf->SetFont('Arial', '', 10);
$texto1 = utf8_decode("El usuario $nombre, con matrícula $matricula, confirma haber recibido este documento que certifica la creación de su cuenta en el sistema.");
$texto2 = utf8_decode("En caso de no recibir este documento en el momento de su creación, el usuario cuenta con un plazo de 10 días hábiles para confirmar la recepción de estas credenciales que avala su acceso al sistema.");

$pdf->MultiCell(0, 7, $texto1);
$pdf->Ln(2);
$pdf->MultiCell(0, 7, $texto2);
$pdf->Ln(10);

// Tabla de credenciales
$pdf->SetX(($pdf->GetPageWidth() - 100) / 2);
$pdf->SetFillColor(200, 200, 200);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(90, 8, utf8_decode('Credenciales de Acceso'), 1, 1, 'C', true);
$pdf->SetX(($pdf->GetPageWidth() - 100) / 2);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(45, 8, 'Usuario', 1);
$pdf->Cell(45, 8, $usuario, 1, 1);
$pdf->SetX(($pdf->GetPageWidth() - 100) / 2);
$pdf->Cell(45, 8, utf8_decode('Contraseña'), 1);
$pdf->Cell(45, 8, $contrasena, 1, 1);
$pdf->Ln(15);

// LÍNEA SEPARADORA ANTES DE LA FIRMA
$pdf->Cell(0, 0, "", "B", 1, "C");
$pdf->Ln(10);

// SECCIÓN DE FIRMA MEJORADA
$pdf->SetFont("Arial", "B", 11);
$pdf->Cell(0, 8, utf8_decode("Recibe"), 0, 1, "C");
$pdf->Ln(12);
$pdf->Cell(0, 8, "________________________________", 0, 1, "C");
$pdf->Ln(3);
$pdf->Cell(0, 8, "__________________", 0, 1, "C");
$pdf->SetFont("Arial", "", 10);
$pdf->Cell(0, 6, utf8_decode("Firma y Fecha"), 0, 1, "C");

// Agregamos más espacio antes del pie de página
$pdf->Ln(30); 

$pdf->SetY(-50); // Ajustar este valor según se necesite

// PIE DE PÁGINA CON INFORMACIÓN INSTITUCIONAL
$pdf->Ln(15);
$pdf->SetFont("Arial", "I", 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->MultiCell(0, 6, utf8_decode("Este documento certifica la creación de la cuenta de acceso al sistema institucional. Para más información, comuníquese con la administración académica."), 0, "C");

// DESCARGAR PDF 

$pdf->Output("D", "Credenciales_$id.pdf");