<?php
session_start();
include("Funcionamiento/lista.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="Interfaz/css/style2.css">
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
            <li><a href="dashboard.php"><i class="fas fa-home"></i> <span class="text">Inicio</span></a></li>
            <li><a href="#"><i class="fas fa-book"></i> <span class="text">Materias</span></a></li>
            <li><a href="#"><i class="fas fa-chart-line"></i> <span class="text">Estadísticas</span></a></li>
            <li><a href="#"><i class="fas fa-clipboard-check"></i> <span class="text">Calificación</span></a></li>
            <li><a href="#"><i class="fas fa-bell"></i> <span class="text">Notificaciones</span></a></li>
            <li><a href="#"><i class="fas fa-users"></i> <span class="text">Lista de usuarios</span></a></li>
        </ul>
    </div> 

    <!-- Contenido Principal -->
    <div class="content" id="content">
        <div class="container mt-4">
            <h2 class="mb-4">Lista de Usuarios</h2>

            <!-- Barra de Búsqueda y Botones -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="input-group w-50">
                    <input type="text" class="form-control small-search" placeholder="Buscar...">
                    <button class="btn btn-primary"><i class="fas fa-search"></i></button>
                </div>
                <div>
                <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#crearAlumnoModal">
                    <i class="fas fa-user-plus"></i> Crear Alumno
                </button>
                    <a href="crear_docente.php" class="btn btn-info"><i class="fas fa-chalkboard-teacher"></i> Crear Docente</a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Rol</th>
                            <th>Carrera</th>
                            <th>Matrícula</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaUsuarios">
                        <?php
                        if (mysqli_num_rows($resultado) > 0):
                            while ($fila = mysqli_fetch_assoc($resultado)): ?>
                                <tr>
                                    <td><?php echo $fila['usuario_id']; ?></td>
                                    <td><?php echo $fila['nombre']; ?></td>
                                    <td><?php echo $fila['rol']; ?></td>
                                    <td><?php echo $fila['carrera'] ? $fila['carrera'] : 'N/A'; ?></td>
                                    <td><?php echo $fila['matricula'] ? $fila['matricula'] : 'N/A'; ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Editar</button>
                                        <button class="btn btn-danger btn-sm eliminar-usuario" data-id="<?php echo $fila['usuario_id']; ?>">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No hay usuarios registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

        <!-- Modal Crear Estudiante -->
    <div class="modal fade" id="crearAlumnoModal" tabindex="-1" aria-labelledby="crearAlumnoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="crearAlumnoModalLabel">
                        <i class="fas fa-user-plus"></i> Registrar Nuevo Estudiante
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <!-- FORMULARIO ACTUALIZADO -->
                    <form id="formCrearAlumno" method="POST" action="Funcionamiento/procesar_alumno.php" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label"><i class="fas fa-user"></i> Nombre Completo</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="correo" class="form-label"><i class="fas fa-envelope"></i> Correo Electrónico</label>
                                <input type="email" class="form-control" id="correo" name="correo" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label"><i class="fas fa-lock"></i> Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label"><i class="fas fa-phone"></i> Teléfono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="matricula" class="form-label"><i class="fas fa-id-card"></i> Matrícula</label>
                                <input type="text" class="form-control" id="matricula" name="matricula" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="domicilio" class="form-label"><i class="fas fa-map-marker-alt"></i> Domicilio</label>
                                <input type="text" class="form-control" id="domicilio" name="domicilio">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="carrera_id" class="form-label"><i class="fas fa-graduation-cap"></i> Carrera</label>
                                <select class="form-select" id="carrera_id" name="carrera_id" required>
                                    <option value="">Seleccione una carrera</option>
                                    <option value="1">Ingeniería en Sistemas Computacionales</option>
                                    <option value="2">Administración de Empresas</option>
                                    <option value="3">Contaduría Pública</option>
                                    <option value="4">Derecho</option>
                                    <option value="5">Psicología</option>
                                    <option value="6">Arquitectura</option>
                                    <option value="7">Diseño Gráfico</option>
                                    <option value="8">Ingeniería Civil</option>
                                    <option value="9">Medicina</option>
                                    <option value="10">Enfermería</option>
                                    <option value="11">Marketing Digital</option>
                                    <option value="12">Turismo</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="curp" class="form-label"><i class="fas fa-file"></i> CURP</label>
                                <input type="file" class="form-control" id="curp" name="curp" accept=".jpg,.jpeg,.png,.pdf">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="certificado_preparatoria" class="form-label"><i class="fas fa-file-alt"></i> Certificado de preparatoria</label>
                                <input type="file" class="form-control" id="certificado_preparatoria" name="certificado_preparatoria" accept=".jpg,.jpeg,.png,.pdf">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="comprobante_pago" class="form-label"><i class="fas fa-receipt"></i> Comprobante de pago</label>
                                <input type="file" class="form-control" id="comprobante_pago" name="comprobante_pago" accept=".jpg,.jpeg,.png,.pdf">
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Registrar</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="Interfaz/js/script2.js"></script>

        <!-- Modal de éxito -->
    <div class="modal fade" id="registroExitosoModal" tabindex="-1" aria-labelledby="registroExitosoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="registroExitosoModalLabel">Registro exitoso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    ¡El alumno ha sido registrado correctamente!
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="window.location.href='lista.php'">Aceptar</button>
                </div>
            </div>
        </div>
    </div>

        <!-- Modal de Error -->
    <div class="modal fade" id="errorRegistroModal" tabindex="-1" aria-labelledby="errorRegistroModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="errorRegistroModalLabel">Error en el Registro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="errorModalBody">
                    <!-- Aquí se insertarán los errores dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

</body>
</html>