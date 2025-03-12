document.addEventListener("DOMContentLoaded", function () {
    const toggleSidebar = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");
    const content = document.getElementById("content");
    const notificacionesDiv = document.getElementById("notificacionesDiv");

    toggleSidebar.addEventListener("click", function () {
        sidebar.classList.toggle("collapsed");
        content.classList.toggle("collapsed");
    });

    // Función para cargar notificaciones
    function cargarNotificaciones() {
        console.log("Cargando notificaciones..."); // Debug

        fetch("../Funcionamiento/obtener_notificaciones.php")
            .then(response => response.json())
            .then(data => {
                console.log("Datos recibidos:", data); // Debug

                if (!notificacionesDiv) {
                    console.error("Elemento de notificaciones no encontrado.");
                    return;
                }

                notificacionesDiv.innerHTML = ""; // Limpiar el contenido anterior

                if (!Array.isArray(data)) {
                    console.error("Respuesta inesperada:", data);
                    notificacionesDiv.innerHTML = "<p>Error al obtener notificaciones.</p>";
                    return;
                }

                if (data.length === 0) {
                    notificacionesDiv.innerHTML = "<p>No tienes nuevas notificaciones.</p>";
                    return;
                }

                // Mostrar notificaciones
                data.forEach(notificacion => {
                    const notifItem = document.createElement("div");
                    notifItem.classList.add("notificacion-item");

                    notifItem.innerHTML = `
                        <p>${notificacion.mensaje} (${notificacion.tipo})</p>
                        <button class="marcar-leido" data-id="${notificacion.id}">Marcar como leído</button>
                    `;

                    notificacionesDiv.appendChild(notifItem);
                });

                // Agregar eventos a los botones de marcar como leído
                document.querySelectorAll(".marcar-leido").forEach(button => {
                    button.addEventListener("click", function () {
                        const notifId = this.getAttribute("data-id");
                        marcarNotificacionLeida(notifId);
                    });
                });
            })
            .catch(error => {
                console.error("Error al obtener notificaciones:", error);
                notificacionesDiv.innerHTML = "<p>Error al obtener notificaciones.</p>";
            });
    }

    // Función para marcar notificación como leída
    function marcarNotificacionLeida(id) {
        fetch("../Funcionamiento/marcar_notificacion.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log(`Notificación ${id} marcada como leída.`);
                cargarNotificaciones();
            } else {
                console.error("Error al marcar como leído:", data.error);
            }
        })
        .catch(error => {
            console.error("Error en la petición:", error);
        });
    }

    // Cargar notificaciones al inicio
    if (notificacionesDiv) {
        cargarNotificaciones();
    }
});