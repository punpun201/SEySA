<?php 
session_start();
include("Funcionamiento/perfil_logic.php");

$id_usuario = $_SESSION['id_usuario'];

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php?error=Debes iniciar sesión.');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="Interfaz/css/perfil.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-left">
            <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Inicio</a>
        </div>
        </a>
        <a href="Funcionamiento/db/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
    </nav>

    <!-- Contenido Principal -->
    <div class="perfil-container">
        <h2>Perfil de Usuario</h2>
        <div class="perfil-card">
            <div class="perfil-info">
                <p><strong>Nombre:</strong> <?php echo $usuario['nombre']; ?></p>
                <p><strong>Teléfono:</strong> <?php echo $usuario['telefono']; ?></p>
                <p><strong>Correo:</strong> <?php echo $usuario['correo']; ?></p>
                <p><strong>Contraseña:</strong> ********</p>

                <?php if ($es_alumno): ?>
                    <p><strong>Matrícula:</strong> <?php echo $usuario['matricula']; ?></p>
                    <p><strong>ID de Carrera:</strong> <?php echo $usuario['id_carrera']; ?></p>
                <?php endif; ?>

                <a href="editar_perfil.php" class="btn">Editar Perfil</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>