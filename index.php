<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de sesión</title>
    <link rel="stylesheet" href="Interfaz/css/style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="formulario">
        <form method="post" action="Funcionamiento/login.php" class="inicio" onsubmit="return validarFormulario()"> 
        <div class="logo-container">
                <img src="Interfaz/img/mi-espacio.png" alt="Logo del sistema" class="logo">
        </div>
            <h1>Iniciar sesión</h1>
            <div id="usuario" class="usuario">
                <input type="text" id="usuarioInput" name="correo" required> 
                <label for="usuario">Correo</label>
            </div>
            <div id="contraseña" class="contraseña">
                <input type="password" id="contraseñaInput" name="contraseña" required> 
                <label for="contraseña">Contraseña</label>
            </div>
            <input name="btniniciar" type="submit" value="Iniciar sesión">
           <div class="recordatorio">¿Olvidaste tu contraseña?</div>
            <div class="home-icon">
            </div>
        </form>
    </div>
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Error de inicio de sesión</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalErrorMessage">
                    <!-- Aquí se mostrará el mensaje de error -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        function validarFormulario() {
            const usuario = document.getElementById('usuarioInput').value.trim();
            const contraseña = document.getElementById('contraseñaInput').value.trim();
            let mensajeError = '';

            if (!usuario) {
                mensajeError = 'El campo de usuario no puede estar vacío.';
            } else if (!contraseña) {
                mensajeError = 'El campo de contraseña no puede estar vacío.';
            } 

            if (mensajeError) {
                mostrarError(mensajeError);
                return false; // Evita el envío del formulario
            }

            return true; // Permite el envío si no hay errores
        }

        function mostrarError(mensaje) {
            document.getElementById('modalErrorMessage').innerHTML = mensaje;
            $('#errorModal').modal('show');
        }

        // Mostrar mensajes de error enviados desde PHP (opcional)
        document.addEventListener('DOMContentLoaded', function() {
            const errorMessage = new URLSearchParams(window.location.search).get('error');
            if (errorMessage) {
                mostrarError(errorMessage);
            }
        });
    </script>
</body>
</html>