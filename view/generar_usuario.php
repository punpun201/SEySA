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
    <title>Registro de Cuentas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Interfaz/css/style3.css">
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
                <li><a href="#"><i class="fas fa-chart-line"></i> <span class="text">Estadísticas</span></a></li>
            <?php endif; ?>
            <?php if ($es_alumno || $es_docente): ?>
                <li><a href="Calificacion.php"><i class="fas fa-clipboard-check"></i> <span class="text">Calificación</span></a></li>
            <?php endif; ?>
            <?php if ($es_alumno || $es_docente): ?>
                <li><a href="notificacion.php"><i class="fas fa-bell"></i> <span class="text">Notificaciones</span></a></li>
            <?php endif; ?>
            <?php if ($es_admin): ?>
                <li><a href="#"><i class="fas fa-cogs"></i> <span class="text">Reportes</span></a></li>
                <li><a href="generar_usuario.php" data-section="lista"><i class="fas fa-cogs"></i> <span class="text">Crear usuario</span></a></li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="container mt-5">
    <h2 class="text-center">Registro de Cuentas</h2>
    <ul class="nav nav-tabs" id="tabRegistro" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="alumno-tab" data-bs-toggle="tab" data-bs-target="#alumno" type="button" role="tab">Registrar Alumno</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="docente-tab" data-bs-toggle="tab" data-bs-target="#docente" type="button" role="tab">Registrar Docente</button>
        </li>
    </ul>
        <div class="tab-content mt-3" id="tabContent">
            <!-- Registro de Alumno -->
            <div class="tab-pane fade show active" id="alumno" role="tabpanel">
                <form id="formAlumno">
                    <div class="row">
                        <!-- ID y botón de búsqueda -->
                        <div class="col-md-6 mb-3">
                            <label for="idAlumno" class="form-label">ID del Alumno:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="idAlumno" required>
                                <button type="button" class="btn btn-primary" onclick="buscarAlumno()">Buscar</button>
                            </div>
                        </div>
                    </div>
                    
                    <div id="datosAlumno" style="display:none;">
                        <div class="row">
                            <!-- Primera columna -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre:</label>
                                <input type="text" class="form-control" id="nombreAlumno" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Teléfono:</label>
                                <input type="text" class="form-control" id="telefonoAlumno" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CURP:</label>
                                <input type="text" class="form-control" id="curpAlumno" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Domicilio:</label>
                                <input type="text" class="form-control" id="domicilioAlumno" readonly>
                            </div>
                            <!-- Segunda columna -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Certificado Preparatoria:</label>
                                <input type="text" class="form-control" id="certificadoAlumno" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Comprobante de Pago:</label>
                                <input type="text" class="form-control" id="comprobanteAlumno" readonly>
                            </div>
                        </div>

                        <!-- Usuario y Contraseña en una fila separada -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Usuario:</label>
                                <input type="text" class="form-control" id="usuarioAlumno" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contraseña:</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="passwordAlumno" readonly>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('passwordAlumno')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="text-center">
                            <button type="button" class="btn btn-success" onclick="guardarCuenta('alumno')">Guardar Cuenta</button>
                            <button type="button" class="btn btn-danger text-white" onclick="generarPDF('alumno')">Generar PDF</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Registro de Docente -->
            <div class="tab-pane fade" id="docente" role="tabpanel">
                <form id="formDocente">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="idDocente" class="form-label">ID del Docente:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="idDocente" required>
                                <button type="button" class="btn btn-primary" onclick="buscarDocente()">Buscar</button>
                            </div>
                        </div>
                    </div>

                    <div id="datosDocente" style="display:none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre:</label>
                                <input type="text" class="form-control" id="nombreDocente" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Teléfono:</label>
                                <input type="text" class="form-control" id="telefonoDocente" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">RFC:</label>
                                <input type="text" class="form-control" id="rfcDocente" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Usuario:</label>
                                <input type="text" class="form-control" id="usuarioDocente" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contraseña:</label>
                                <div class="input-group"> 
                                    <input type="password" class="form-control" id="passwordDocente" readonly>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('passwordDocente')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="button" class="btn btn-success" onclick="guardarCuenta('docente')">Guardar Cuenta</button>
                            <button type="button" class="btn btn-danger text-white" onclick="generarPDF('docente')">Generar PDF</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Notificaciones -->
    <div class="modal fade" id="modalNotificacion" tabindex="-1" aria-labelledby="modalNotificacionLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modalNotificacionLabel">Notificación</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body" id="modalMensaje">
            <!-- Aquí se insertará el mensaje dinámicamente -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
        </div>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../Interfaz/js/script3.js"></script>
</body>
</html>