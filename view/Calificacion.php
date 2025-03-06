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
    <link rel="stylesheet" href="../Interfaz/css/style2.css">
</head>
<body data-usuario="<?php echo $es_docente ? 'docente' : 'alumno'; ?>">

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

    <!-- Contenido -->
    <div class="content" id="content">
        <div class="container mt-4">
            <h2 class="text-center mb-4"><i class="fas fa-graduation-cap"></i> Calificaciones</h2>

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

                <?php if ($es_docente): ?>
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

            <!-- Para alumnos -->
            <?php if ($es_alumno): ?>
            <div id="vistaAlumno">
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-book-open"></i> Mis calificaciones</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Materia</th>
                                        <th>Parcial 1</th>
                                        <th>Parcial 2</th>
                                        <th>Parcial 3</th>
                                        <th>Calificación final</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaAlumno">
                                    <!-- Datos dinámicos -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        <!-- Para docentes -->
        <?php if ($es_docente): ?>
        <div id="vistaDocente">
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Registro de Calificaciones</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Alumno</th>
                                    <th>Parcial 1</th>
                                    <th>Parcial 2</th>
                                    <th>Parcial 3</th>
                                    <th>Calificación Final</th>
                                </tr>
                            </thead>
                            <tbody id="tablaDocente">
                                
                            </tbody>
                        </table>
                            <div class="text-center mt-3">
                                <button id="guardarTodasCalificaciones" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar calificaciones
                                </button>
                            </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        </div>
    </div>

    <!-- Modal de Confirmación -->
    <div class="modal fade" id="modalCalificacionGuardada" tabindex="-1" aria-labelledby="modalCalificacionGuardadaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header bg-success text-white">
            <h5 class="modal-title" id="modalCalificacionGuardadaLabel"><i class="fas fa-check-circle"></i> Calificación Guardada</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
            La calificación ha sido registrada con éxito.
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
        </div>
        </div>
    </div>
    </div>

    <!-- Modal de Comentarios -->
    <div class="modal fade" id="modalComentarios" tabindex="-1" aria-labelledby="modalComentariosLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalComentariosLabel">Comentario para <span id="comentarioAlumnoNombre"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <textarea id="comentarioTexto" class="form-control" rows="4" placeholder="Escribe un comentario..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" id="guardarComentario" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../Interfaz/js/script.js"></script>
</body>
</html>