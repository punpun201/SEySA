document.addEventListener("DOMContentLoaded", function() {
    // Manejo de la barra lateral
    const toggleSidebar = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");
    const content = document.getElementById("content");

    if (toggleSidebar) {
        toggleSidebar.addEventListener("click", function() {
            sidebar.classList.toggle("collapsed");
            content.classList.toggle("collapsed");
        });
    }

    // Manejo de la eliminación de usuarios
    document.querySelectorAll(".eliminar-usuario").forEach(button => {
        button.addEventListener("click", function() {
            let userId = this.getAttribute("data-id");

            if (confirm("¿Estás seguro de que quieres dar de baja a este usuario?")) {
                fetch("../Funcionamiento/eliminar_usuario.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "id_usuario=" + userId
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        location.reload(); // Recargar la página para reflejar cambios
                    }
                })
                .catch(error => console.error("Error:", error));
            }
        });
    });

    // Manejo de los modales de éxito y error
    const urlParams = new URLSearchParams(window.location.search);

    // Mostrar modal de éxito si el registro fue exitoso
    if (urlParams.has("registro")) {
        var registroModal = new bootstrap.Modal(document.getElementById('registroExitosoModal'));
        registroModal.show();
    }

    // Mostrar modal de error si hay errores
    if (urlParams.has("errores")) {
        var errores = JSON.parse(decodeURIComponent(urlParams.get("errores")));
        var errorHtml = "<ul>";
        errores.forEach(error => {
            errorHtml += `<li>${error}</li>`;
        });
        errorHtml += "</ul>";

        document.getElementById("errorModalBody").innerHTML = errorHtml;
        var errorModal = new bootstrap.Modal(document.getElementById('errorRegistroModal'));
        errorModal.show();
    }
});