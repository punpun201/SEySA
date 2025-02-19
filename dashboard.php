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
    <link rel="stylesheet" href="Interfaz/css/style2.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <button class="toggle-btn" id="toggleSidebar"><i class="fas fa-bars"></i></button>
        <div class="profile">
            <a href="perfil.php" class="profile-link">
                <img src="img/user-profile.png" alt="Perfil">
                <span><?php echo $_SESSION['nombre']; ?></span>
            </a>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <ul>
            <li><a href="dashboard.php" data-section="inicio"><i class="fas fa-home"></i> <span class="text">Inicio</span></a></li>
            <li><a href="#" data-section="materias"><i class="fas fa-book"></i> <span class="text">Materias</span></a></li>
            <li><a href="#" data-section="estadisticas"><i class="fas fa-chart-line"></i> <span class="text">Estadísticas</span></a></li>
            <li><a href="#" data-section="calificacion"><i class="fas fa-clipboard-check"></i> <span class="text">Calificación</span></a></li>
            <li><a href="#" data-section="notificaciones"><i class="fas fa-bell"></i> <span class="text">Notificaciones</span></a></li>
            <?php if ($es_admin): ?>
                <li><a href="lista.php" data-section="lista"><i class="fas fa-cogs"></i> <span class="text">Lista de usuarios</span></a></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Contenido Principal -->
    <div class="content" id="content">
        <div class="container mt-4">
            <div class="row">
                <div class="col-md-4">
                    <div class="card info-card">
                        <i class="fas fa-user-graduate"></i>
                        <h5>Alumnos Registrados</h5>
                        <p>320</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card info-card">
                        <i class="fas fa-book"></i>
                        <h5>Materias Activas</h5>
                        <p>18</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card info-card">
                        <i class="fas fa-chart-pie"></i>
                        <h5>Alumnos en Riesgo</h5>
                        <p>45</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="Interfaz/js/script.js"></script>

</body>
</html>