document.addEventListener("DOMContentLoaded", function() {
    const toggleSidebar = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");
    const content = document.querySelector(".container");

    toggleSidebar.addEventListener("click", function() {
        sidebar.classList.toggle("collapsed");
        content.classList.toggle("collapsed");
    });
});

// Función para mostrar el modal con un mensaje
function mostrarModal(mensaje) {
    document.getElementById("modalMensaje").innerText = mensaje;
    let modal = new bootstrap.Modal(document.getElementById("modalNotificacion"));
    modal.show();
}

// Función para buscar alumno
function buscarAlumno() {
    const id = document.getElementById("idAlumno").value.trim();

    if (id === "") {
        mostrarModal("Por favor, ingrese un ID de alumno.");
        return;
    }

    fetch(`Funcionamiento/buscar_usuario.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                mostrarModal(data.error);
            } else if (data.tipo === "alumno") {
                document.getElementById("nombreAlumno").value = data.nombre;
                document.getElementById("telefonoAlumno").value = data.telefono;
                document.getElementById("curpAlumno").value = data.curp;
                document.getElementById("domicilioAlumno").value = data.domicilio;
                document.getElementById("certificadoAlumno").value = data.certificado_preparatoria;
                document.getElementById("comprobanteAlumno").value = data.comprobante_pago;
                document.getElementById("usuarioAlumno").value = data.usuario;
                document.getElementById("passwordAlumno").value = data.contraseña;

                document.getElementById("datosAlumno").style.display = "block";
            } else {
                mostrarModal("El ID ingresado no corresponde a un alumno.");
            }
        })
        .catch(error => console.error("Error en la búsqueda:", error));
}

// Función para buscar docente
function buscarDocente() {
    const id = document.getElementById("idDocente").value.trim();

    if (id === "") {
        mostrarModal("Por favor, ingrese un ID de docente.");
        return;
    }

    fetch(`Funcionamiento/buscar_usuario.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                mostrarModal(data.error);
            } else if (data.tipo === "docente") {
                document.getElementById("nombreDocente").value = data.nombre;
                document.getElementById("telefonoDocente").value = data.telefono;
                document.getElementById("rfcDocente").value = data.RFC;
                document.getElementById("usuarioDocente").value = data.usuario;
                document.getElementById("passwordDocente").value = data.contraseña;

                document.getElementById("datosDocente").style.display = "block";
            } else {
                mostrarModal("El ID ingresado no corresponde a un docente.");
            }
        })
        .catch(error => console.error("Error en la búsqueda:", error));
}

// Función para generar PDF de los datos
function generarPDF(tipo) {
    const id = tipo === "alumno" ? document.getElementById("idAlumno").value : document.getElementById("idDocente").value;
    if (id.trim() === "") {
        alert("Primero busque un usuario antes de generar el PDF.");
        return;
    }
    window.open(`Funcionamiento/pdf/generar_pdf.php?tipo=${tipo}&id=${id}`, "_blank");
}

// Función para guardar cuenta
function guardarCuenta(tipo) {
    const id = tipo === "alumno" ? document.getElementById("idAlumno").value : document.getElementById("idDocente").value;
    const usuario = tipo === "alumno" ? document.getElementById("usuarioAlumno").value : document.getElementById("usuarioDocente").value;
    const contraseña = tipo === "alumno" ? document.getElementById("passwordAlumno").value : document.getElementById("passwordDocente").value;

    if (id.trim() === "" || usuario.trim() === "" || contraseña.trim() === "") {
        mostrarModal("Debe buscar un usuario antes de guardar la cuenta.");
        return;
    }

    fetch("Funcionamiento/guardar_usuario.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ tipo, id, usuario, contraseña }) // Se envían todos los parámetros
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarModal("Cuenta guardada correctamente.");
        } else {
            mostrarModal("Error al guardar la cuenta: " + data.error);
        }
    })
    .catch(error => console.error("Error en la solicitud:", error));
}