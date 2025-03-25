<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php?error=Debes iniciar sesión.');
    exit();
}

$roles_usuario = $_SESSION['roles'] ?? [];
$es_admin = in_array("Administrador", (array)$roles_usuario);
$es_docente = in_array("Docente", (array)$roles_usuario);
$es_alumno = in_array("Alumno", (array)$roles_usuario);
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
    <link rel="stylesheet" href="../Interfaz/css/style7.css">
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

    <div class="content">
        <div class="page-header">
            <h2><i class="fa-solid fa-print"></i> Generación de reportes</h2>
            <p>Seleccione el tipo de reporte que desea generar.</p>
        </div>
        <div class="page-header">
        
        <select id="tipo-reporte" class="form-select mb-4 w-50 mx-auto">
            <option value="">Selecciona una opción</option>
            <option value="docente">Por Docente</option>
            <option value="grupo">Por Grupo</option>
            <option value="global">Global</option>
        </select>
    </div>

    <!-- Contenedores por tipo -->
    <div id="reporte-docente" class="reporte-section" style="display:none;">
        <h4>📋 Reporte por Docente</h4>
        <div class="mb-4">
            <label for="periodo-docente" class="form-label"><i class="fas fa-calendar-alt"></i> Selecciona el período:</label>
            <select id="periodo-docente" class="form-select w-50 mx-auto">
                <option value="">Selecciona un período</option>
            </select>
        </div>

        <div class="table-responsive mt-4">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Matrícula</th>
                        <th>Nombre del Docente</th>
                        <th>Materia</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="tabla-docentes">
                    <tr><td colspan="4">Seleccione un período para ver los docentes.</td></tr>
                </tbody>
            </table>
        </div>

    <div id="reporte-grupo" class="reporte-section" style="display:none;">
        <h4>👥 Reporte por Grupo</h4>
        <!-- Aquí va el contenido del reporte por grupo -->
    </div>

    <div id="reporte-global" class="reporte-section" style="display:none;">
        <h4>🌐 Reporte Global</h4>
        <!-- Aquí va el contenido del reporte global -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../Interfaz/js/script7.js"></script>
</body>
</html>