document.addEventListener("DOMContentLoaded", function() {
    const toggleSidebar = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");
    const content = document.querySelector(".content");
    const periodoSelect = document.getElementById("periodo");
    const materiaSelect = document.getElementById("materiaSelect");
    const alumnoLista = document.getElementById("alumnoLista");

    toggleSidebar.addEventListener("click", function() {
        sidebar.classList.toggle("collapsed");
        content.classList.toggle("collapsed");
    });

    // Cargar períodos disponibles
    fetch("../Funcionamiento/obtener_periodos.php")
        .then(response => response.json())
        .then(data => {
            periodoSelect.innerHTML = '<option value="">Seleccione un período</option>';
            data.forEach(periodo => {
                periodoSelect.innerHTML += `<option value="${periodo.id}">${periodo.nombre}</option>`;
            });
        })
        .catch(error => console.error("Error cargando períodos:", error));

    // Cargar materias según el período seleccionado
    periodoSelect.addEventListener("change", function() {
        const periodoId = this.value;
        if (!periodoId) return;

        fetch("../Funcionamiento/obtener_materias_profesor.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `periodo_id=${encodeURIComponent(periodoId)}`
        })
        .then(response => response.json())
        .then(data => {
            materiaSelect.innerHTML = '<option value="">Seleccione una materia</option>';
            data.forEach(materia => {
                materiaSelect.innerHTML += `<option value="${materia.id}">${materia.nombre}</option>`;
            });
        })
        .catch(error => console.error("Error cargando materias:", error));
    });

    // Cargar alumnos según la materia seleccionada
    materiaSelect.addEventListener("change", function() {
        const materiaId = this.value;
        const periodoId = periodoSelect.value;
        if (!materiaId || !periodoId) return;

        fetch("../Funcionamiento/obtener_alumnos.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `materia_id=${encodeURIComponent(materiaId)}&periodo_id=${encodeURIComponent(periodoId)}`
        })
        .then(response => response.json())
        .then(data => {
            alumnoLista.innerHTML = "";
            if (data.length === 0) {
                alumnoLista.innerHTML = `<tr><td colspan="3" class="text-center">No hay alumnos inscritos en esta materia</td></tr>`;
                return;
            }
            data.forEach(alumno => {
                alumnoLista.innerHTML += `
                    <tr>
                        <td>${alumno.matricula}</td>
                        <td>${alumno.nombre}</td>
                        <td>
                            <button class="btn generate-report"
                                data-matricula="${alumno.matricula}"
                                data-materia="${materiaId}"
                                data-periodo="${periodoId}">
                                <i class="fas fa-file-pdf"></i> Generar
                            </button>
                        </td>
                    </tr>`;
            });

            // Agregar evento a los botones de generación de PDF
            document.querySelectorAll(".generate-report").forEach(button => {
                button.addEventListener("click", function () {
                    const matricula = this.getAttribute("data-matricula");
                    const materiaId = this.getAttribute("data-materia");
                    const periodoId = this.getAttribute("data-periodo");
            
                    console.log("Intentando generar PDF con:", { matricula, materiaId, periodoId });
            
                    if (!matricula || !materiaId || !periodoId) {
                        alert("Error: Faltan datos para generar el PDF.");
                        return;
                    }
            
                    window.open(`../Funcionamiento/pdf/reporte_pdf.php?matricula=${matricula}&materia_id=${materiaId}&periodo_id=${periodoId}`, "_blank");
                });
            });            
        })
        .catch(error => console.error("Error cargando alumnos:", error));
    });
});