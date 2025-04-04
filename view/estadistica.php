<?php
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
    <link rel="stylesheet" href="../Interfaz/css/style5.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    
    <!-- Contenido -->
    <div class="content" id="content">
        <div class="container mt-4">
            <h2 class="text-center mb-4"><i class="fas fa-chart-line"></i> Estadísticas</h2>

            <!-- Selector de período -->
            <div class="selectores-container d-flex gap-3">
                <div class="selector-box w-50">
                    <label for="periodo" class="form-label">
                        <i class="fas fa-calendar-alt"></i> Selecciona el período:
                    </label>
                    <select id="periodo" class="form-select" data-usuario="<?php echo $es_docente ? 'docente' : 'alumno'; ?>">
                        <option value="">Selecciona un período</option>
                    </select>
                </div>

                <?php if ($es_admin || $es_docente): ?>
                <div class="selector-box w-50">
                    <label for="materiaGrupo" class="form-label">
                        <i class="fas fa-book"></i> Materias asignadas:
                    </label>
                    <select id="materiaGrupo" class="form-select">
                        <option value="">Selecciona una materia</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>

        <div id="statsContainer" class="stats-container">
            <div class="card"><h3>Inscritos</h3><p id="numInscritos">-</p></div>
            <div class="card"><h3>Aprobados</h3><p id="numAprobados">-</p></div>
            <div class="card"><h3>En Riesgo</h3><p id="numRiesgo">-</p></div>
            <div class="card"><h3>Reprobados</h3><p id="numReprobados">-</p></div>
            <div class="card"><h3>Promedio</h3><p id="promedioGeneral">-</p></div>
        </div>

        <div id="estadisticas-container" class="chart-container">
            <canvas id="graficoRendimiento"></canvas>
        </div>

        <div class="text-end my-3">
            <button id="exportarPDF" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Exportar PDF
            </button>
        </div>
        
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="../Interfaz/js/script5.js"></script>

</body>
</html>