<?php
include("../Funcionamiento/db/conexion.php");
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php?error=Debes iniciar sesión.');
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

$query = "SELECT correo, contraseña FROM usuarios WHERE id = '$id_usuario'";
$resultado = mysqli_query($conexion, $query);

if (!$resultado || mysqli_num_rows($resultado) === 0) {
    die("Error al obtener los datos del usuario.");
}

$usuario = mysqli_fetch_assoc($resultado);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../Interfaz/css/style3.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <button class="toggle-btn" id="toggleSidebar"><i class="fas fa-bars"></i></button>
        <div class="profile">
            <a href="../Funcionamiento/db/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-home"></i> <span class="text">Inicio</span></a></li>
        </ul>
    </div>

    <!-- Contenido de la cuenta -->
    <div class="container mt-5">
        <div class="card p-4 shadow cuenta-container mx-auto" style="max-width: 400px;">
            <h2 class="text-center">Mi Cuenta</h2>
            <div class="mb-3">
                <label class="form-label"><strong>Correo:</strong></label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($usuario['correo']); ?>" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label"><strong>Contraseña:</strong></label>
                <div class="input-group">
                    <input type="password" class="form-control" id="passwordField" value="<?php echo htmlspecialchars($usuario['contraseña']); ?>" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <div class="text-center">
                <button class="btn btn-primary w-auto px-3" onclick="cambiarContrasena()">Cambiar Contraseña</button>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Cambio -->
    <div class="modal fade" id="modalCambio" tabindex="-1" aria-labelledby="modalCambioLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCambioLabel">Confirmar Cambio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="password" class="form-control" id="nuevaContrasena" placeholder="Nueva contraseña">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="confirmarCambio()">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Error -->
    <div class="modal fade" id="modalError" tabindex="-1" aria-labelledby="modalErrorLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalErrorLabel">Error</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p id="errorMensaje"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Interfaz/js/script4.js"></script>
</body>
</html>