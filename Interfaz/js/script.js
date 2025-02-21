document.addEventListener("DOMContentLoaded", function() {
    const toggleSidebar = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");
    const content = document.getElementById("content");

    // Manejar colapso del sidebar
    toggleSidebar.addEventListener("click", function() {
        sidebar.classList.toggle("collapsed");
        content.classList.toggle("collapsed");
    });

    // Determinar tipo de usuario y mostrar la vista correcta
    let usuarioTipo = "docente"; // Esto debe venir dinámicamente
    let vistaAlumno = document.getElementById("vistaAlumno");
    let vistaDocente = document.getElementById("vistaDocente");

    if (usuarioTipo === "alumno") {
        vistaAlumno.classList.remove("d-none");
    } else if (usuarioTipo === "docente") {
        vistaDocente.classList.remove("d-none");
    }
});