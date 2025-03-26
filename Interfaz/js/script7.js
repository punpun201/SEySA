document.addEventListener("DOMContentLoaded", function () {
    const toggleSidebar = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");
    const content = document.querySelector(".content");
    const tipoReporte = document.getElementById("tipo-reporte");
    const periodoDocente = document.getElementById("periodo-docente");
    const tablaDocentes = document.getElementById("tabla-docentes");

    toggleSidebar.addEventListener("click", function () {
        sidebar.classList.toggle("collapsed");
        content.classList.toggle("collapsed");
    });

    const secciones = {
        docente: document.getElementById("reporte-docente"),
        grupo: document.getElementById("reporte-grupo"),
        global: document.getElementById("reporte-global")
    };

    tipoReporte.addEventListener("change", function () {
        const seleccion = this.value;
        Object.keys(secciones).forEach(tipo => {
            secciones[tipo].style.display = (tipo === seleccion) ? "block" : "none";
        });

        if (seleccion === "docente") {
            // Cargar periodos solo una vez
            fetch("../Funcionamiento/obtener_periodos_admin.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "listar=true"
            })
                .then(res => res.json())
                .then(data => {
                    // Limpiar antes de cargar
                    periodoDocente.innerHTML = '<option value="">Selecciona un período</option>';
                    data.forEach(p => {
                        periodoDocente.innerHTML += `<option value="${p.id}">${p.nombre}</option>`;
                    });
                });
        }
    });

    // Al seleccionar un período, cargar docentes
    periodoDocente.addEventListener("change", function () {
        const periodoId = this.value;
        if (!periodoId) return;

        fetch(`../Funcionamiento/obtener_periodos_docente.php?periodo_id=${periodoId}`)
            .then(res => res.json())
            .then(data => {
                tablaDocentes.innerHTML = ""; // Limpiar tabla

                if (data.length === 0) {
                    tablaDocentes.innerHTML = `<tr><td colspan="4">No se encontraron docentes en este período.</td></tr>`;
                    return;
                }

                data.forEach(docente => {
                    tablaDocentes.innerHTML += `
                        <tr>
                            <td>${docente.matricula_docente}</td>
                            <td>${docente.nombre}</td>
                            <td>${docente.materia}</td>
                            <td>
                                <button class="btn btn-sm btn-primary generar-reporte-docente" 
                                    data-docente="${docente.matricula_docente}" 
                                    data-periodo="${periodoId}" 
                                    data-materia="${docente.materia_id}">
                                    <i class="fas fa-file-pdf"></i> Generar Reporte
                                </button>
                            </td>
                        </tr>`;
                });
            });
    });

    // Botón de generación de PDF por docente
    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("generar-reporte-docente") || e.target.closest(".generar-reporte-docente")) {
            const btn = e.target.closest(".generar-reporte-docente");
            const docente = btn.dataset.docente;
            const periodo = btn.dataset.periodo;
            const materia = btn.dataset.materia;

            const url = `../Funcionamiento/pdf/reporte_docente.php?matricula_docente=${docente}&periodo_id=${periodo}&materia_id=${materia}`;
            window.open(url, "_blank");
        }
    });
});