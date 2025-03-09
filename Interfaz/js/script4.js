document.addEventListener("DOMContentLoaded", function() {
    const toggleSidebar = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");
    const content = document.getElementById("content");

    if (toggleSidebar) {
        toggleSidebar.addEventListener("click", function () {
            sidebar.classList.toggle("collapsed");
            if (content) {
                content.classList.toggle("collapsed");
            }
        });
    }

    window.togglePassword = function () {
        const passwordField = document.getElementById("passwordField");
        const eyeIcon = document.querySelector("#passwordField + button i");
        if (passwordField.type === "password") {
            passwordField.type = "text";
            eyeIcon.classList.remove("fa-eye");
            eyeIcon.classList.add("fa-eye-slash");
        } else {
            passwordField.type = "password";
            eyeIcon.classList.remove("fa-eye-slash");
            eyeIcon.classList.add("fa-eye");
        }
    };

    function mostrarError(mensaje) {
        document.getElementById("errorMensaje").innerText = mensaje;
        let modalError = new bootstrap.Modal(document.getElementById('modalError'));
        modalError.show();
    }

    // Función para abrir el modal de confirmación
    window.cambiarContrasena = function () {
        let modalCambio = new bootstrap.Modal(document.getElementById('modalCambio'));
        modalCambio.show();
    };

    // Función para confirmar el cambio de contraseña
    window.confirmarCambio = function () {
        const nuevaContrasena = document.getElementById("nuevaContrasena").value;

        if (!/^[a-zA-Z0-9]{4,}$/.test(nuevaContrasena)) {
            mostrarError("La contraseña debe tener solo letras y números, sin espacios ni caracteres especiales, y mínimo 4 caracteres.");
            return;
        }

        fetch("../Funcionamiento/cambiar_contrasena.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ contrasena: nuevaContrasena })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let modalCambio = bootstrap.Modal.getInstance(document.getElementById('modalCambio'));
                modalCambio.hide();  

                let modalExito = new bootstrap.Modal(document.getElementById('modalExito'));
                modalExito.show();
            } else {
                mostrarError(data.error);
            }
        })
        .catch(error => console.error("Error al cambiar la contraseña:", error));
    };
});