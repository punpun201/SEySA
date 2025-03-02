document.addEventListener("DOMContentLoaded", function() {
    const toggleSidebar = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");
    const content = document.querySelector(".container");

    toggleSidebar.addEventListener("click", function() {
        sidebar.classList.toggle("collapsed");
        content.classList.toggle("collapsed");
    });
});

// Función para buscar alumno
function buscarAlumno() {
    const id = document.getElementById("idAlumno").value.trim();

    if (id === "") {
        alert("Por favor, ingrese un ID de alumno.");
        return;
    }

    fetch(`Funcionamiento/buscar_usuario.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
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
                alert("El ID ingresado no corresponde a un alumno.");
            }
        })
        .catch(error => console.error("Error en la búsqueda:", error));
}

// Función para buscar docente
function buscarDocente() {
    const id = document.getElementById("idDocente").value.trim();

    if (id === "") {
        alert("Por favor, ingrese un ID de docente.");
        return;
    }

    fetch(`Funcionamiento/buscar_usuario.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
            } else if (data.tipo === "docente") {
                document.getElementById("nombreDocente").value = data.nombre;
                document.getElementById("telefonoDocente").value = data.telefono;
                document.getElementById("rfcDocente").value = data.RFC;
                document.getElementById("usuarioDocente").value = data.usuario;
                document.getElementById("passwordDocente").value = data.contraseña;

                document.getElementById("datosDocente").style.display = "block";
            } else {
                alert("El ID ingresado no corresponde a un docente.");
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
    window.open(`Funcionamiento/generar_pdf.php?tipo=${tipo}&id=${id}`, "_blank");
}

// Función para guardar cuenta
function guardarCuenta(tipo) {
    const id = tipo === "alumno" ? document.getElementById("idAlumno").value : document.getElementById("idDocente").value;
    if (id.trim() === "") {
        alert("Debe buscar un usuario antes de guardar la cuenta.");
        return;
    }
    
    fetch("Funcionamiento/guardar_usuario.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ tipo, id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Cuenta guardada correctamente.");
        } else {
            alert("Error al guardar la cuenta: " + data.error);
        }
    })
    .catch(error => console.error("Error en la solicitud:", error));
}