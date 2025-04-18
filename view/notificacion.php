<?php
include ("../Funcionamiento/db/conexion.php");

session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php?error=Debes iniciar sesión.');
    exit();
}

$seccion = isset($_GET['seccion']) ? $_GET['seccion'] : 'inicio';

// Verifica los roles del usuario
$es_admin = in_array("Administrador", $_SESSION['roles']);
$es_docente = in_array("Docente", $_SESSION['roles']);
$es_alumno = in_array("Alumno", $_SESSION['roles']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Académico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Interfaz/css/style4.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <button class="toggle-btn" id="toggleSidebar"><i class="fas fa-bars"></i></button>
        <div class="profile">
            <a href="cuenta.php" class="account-btn"><i class="fas fa-user"></i></a>
            <a href="../Funcionamiento/db/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-home"></i> <span class="text">Inicio</span></a></li>
            <?php if ($es_alumno || $es_docente): ?>
                <li><a href="#"><i class="fas fa-book"></i> <span class="text">Materias</span></a></li>
            <?php endif; ?>
            <?php if ($es_docente || $es_admin): ?>
                <li><a href="estadistica.php"><i class="fas fa-chart-line"></i> <span class="text">Estadísticas</span></a></li>
            <?php endif; ?>
            <?php if ($es_alumno || $es_docente): ?>
                <li><a href="Calificacion.php"><i class="fas fa-clipboard-check"></i> <span class="text">Calificación</span></a></li>
            <?php endif; ?>
            <?php if ($es_alumno || $es_docente): ?>
                <li><a href="notificacion.php"><i class="fas fa-bell"></i> <span class="text">Notificaciones</span></a></li>
            <?php endif; ?>
            <?php if ($es_docente): ?>
                <li><a href="reportes.php"><i class="fa-solid fa-print"></i> <span class="text">Reportes</span></a></li>
            <?php endif; ?>
            <?php if ($es_admin): ?>
                <li><a href="rendimiento.php"><i class="fa-solid fa-print"></i> <span class="text">Rendimiento</span></a></li>
            <?php endif; ?>
            <?php if ($es_admin): ?>
                <li><a href="generar_usuario.php" data-section="lista"><i class="fa-solid fa-user-plus"></i><span class="text">Crear usuario</span></a></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Contenedor de notificaciones -->
    <div class="notificaciones">
        <h2 class="titulo-notificaciones">Notificaciones</h2>
        <button id="marcarTodasLeidas" class="btn-marcar-todo">
            <i class="fas fa-check-double"></i> Marcar todas como leídas
        </button>

        <div id="notificacionesDiv" class="notificaciones-container">
            <p>Cargando notificaciones...</p>
        </div>
    </div>

    <!-- Modal de confirmación -->
    <div id="modalConfirmacion" class="modal">
        <div class="modal-contenido">
            <h3 id="mensajeConfirmacion"></h3>
            <div class="modal-botones">
                <button id="confirmarAccion" class="btn-confirmar">Confirmar</button>
                <button id="cancelarAccion" class="btn-cancelar">Cancelar</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../Interfaz/js/script2.js"></script>
</body>
</html>