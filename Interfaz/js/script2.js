document.addEventListener("DOMContentLoaded", function () {
    const toggleSidebar = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");
    const content = document.getElementById("content");
    const notificacionesDiv = document.getElementById("notificacionesDiv");
    const btnMarcarTodas = document.getElementById("marcarTodasLeidas");

    toggleSidebar.addEventListener("click", function () {
        sidebar.classList.toggle("collapsed");
        content.classList.toggle("collapsed");
    });
    
    // Función para cargar notificaciones
    function cargarNotificaciones() {
        fetch('../Funcionamiento/obtener_notificaciones.php')
            .then(response => response.json())
            .then(data => {
                notificacionesDiv.innerHTML = ""; // Limpiar contenido

                if (!data || data.length === 0) {
                    notificacionesDiv.innerHTML = "<p>No tienes notificaciones.</p>";
                    return;
                }

                data.forEach(noti => {
                    let notificacionHTML = `
                        <div class="notificacion ${noti.leido ? 'leida' : ''}" data-id="${noti.id}">
                            <p>${noti.mensaje}</p>
                            <button class="marcar-leida" data-id="${noti.id}">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    `;
                    notificacionesDiv.innerHTML += notificacionHTML;
                });

                // Agregar eventos a los botones de "marcar como leída"
                document.querySelectorAll(".marcar-leida").forEach(button => {
                    button.addEventListener("click", function () {
                        let idNotificacion = this.getAttribute("data-id");
                        mostrarModal("¿Marcar esta notificación como leída?", () => {
                            marcarLeida(idNotificacion);
                        });
                    });
                });
            })
            .catch(error => console.error("Error al obtener notificaciones:", error));
    }

    function marcarLeida(id) {
        fetch('../Funcionamiento/marcar_leida.php', {
            method: 'POST',
            body: new URLSearchParams({ id: id })
        }).then(() => {
            let notificacion = document.querySelector(`.notificacion[data-id='${id}']`);
            if (notificacion) {
                notificacion.style.opacity = "0.3";
            }
        });
    }

    // Botón antes de asignar el evento
    if (btnMarcarTodas) {
        btnMarcarTodas.addEventListener("click", function () {
            mostrarModal("¿Marcar todas las notificaciones como leídas?", function () {
                fetch('../Funcionamiento/marcar_todas_leidas.php', {
                    method: 'POST'
                }).then(() => {
                    document.querySelectorAll(".notificacion").forEach(noti => {
                        noti.style.opacity = "0.3";
                    });
                });
            });
        });
    }

    function mostrarModal(mensaje, callback) {
        let modal = document.getElementById("modalConfirmacion");
        let mensajeConfirmacion = document.getElementById("mensajeConfirmacion");
        let btnConfirmar = document.getElementById("confirmarAccion");
        let btnCancelar = document.getElementById("cancelarAccion");

        if (!modal || !mensajeConfirmacion || !btnConfirmar || !btnCancelar) {
            console.error("No se encontró el modal de confirmación.");
            return;
        }

        mensajeConfirmacion.innerText = mensaje;
        modal.style.display = "flex";

        btnConfirmar.onclick = function () {
            modal.style.display = "none";
            callback();
        };

        btnCancelar.onclick = function () {
            modal.style.display = "none";
        };
    }

    cargarNotificaciones();
});