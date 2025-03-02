document.addEventListener("DOMContentLoaded", function() {
    const toggleSidebar = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");
    const content = document.getElementById("content");

    toggleSidebar.addEventListener("click", function() {
        sidebar.classList.toggle("collapsed");
        content.classList.toggle("collapsed");
    });
})

function buscarAlumno() {
    const id = document.getElementById("idAlumno").value;
    fetch(`buscar_alumno.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert("Alumno no encontrado");
            } else {
                document.getElementById("nombreAlumno").value = data.nombre;
                document.getElementById("usuarioAlumno").value = data.usuario;
                document.getElementById("passwordAlumno").value = data.contrasena;
                document.getElementById("datosAlumno").style.display = "block";
            }
        });
}

function buscarDocente() {
    const id = document.getElementById("idDocente").value;
    fetch(`buscar_docente.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert("Docente no encontrado");
            } else {
                document.getElementById("nombreDocente").value = data.nombre;
                document.getElementById("usuarioDocente").value = data.usuario;
                document.getElementById("passwordDocente").value = data.contrasena;
                document.getElementById("datosDocente").style.display = "block";
            }
        });
}

function generarPDF(tipo) {
    window.open(`generar_pdf.php?tipo=${tipo}&id=${tipo === 'alumno' ? document.getElementById("idAlumno").value : document.getElementById("idDocente").value}`);
}

