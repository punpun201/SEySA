document.addEventListener("DOMContentLoaded", function () {
    const toggleSidebar = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");
    const content = document.querySelector(".content");
    const tipoReporte = document.getElementById("tipo-reporte");
    const periodoDocente = document.getElementById("periodo-docente");
    const tablaDocentes = document.getElementById("tabla-docentes");
    const periodoGrupo = document.getElementById("periodo-grupo");
    const grupoSelect = document.getElementById("grupo-select");
    const generarGrupoBtn = document.getElementById("generar-reporte-grupo");

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
            fetch("../Funcionamiento/obtener_periodos_admin.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "listar=true"
            })
                .then(res => res.json())
                .then(data => {
                    periodoDocente.innerHTML = '<option value="">Selecciona un período</option>';
                    data.forEach(p => {
                        periodoDocente.innerHTML += `<option value="${p.id}">${p.nombre}</option>`;
                    });
                });
        }

        else if (seleccion === "grupo") {
            fetch("../Funcionamiento/obtener_periodos_admin.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "listar=true"
            })
            .then(res => res.json())
            .then(data => {
                periodoGrupo.innerHTML = '<option value="">Selecciona un período</option>';
                data.forEach(p => {
                    periodoGrupo.innerHTML += `<option value="${p.id}">${p.nombre}</option>`;
                });
            });
        
        }
    });

    // DOCENTE
    periodoDocente.addEventListener("change", function () {
        const periodoId = this.value;
        if (!periodoId) return;
    
        fetch(`../Funcionamiento/obtener_periodos_docente.php?periodo_id=${periodoId}`)
            .then(res => res.json())
            .then(data => {
                const contenedorSelect = document.getElementById("docente-select");
                contenedorSelect.innerHTML = '<option value="">Selecciona un docente</option>';
                tablaDocentes.innerHTML = ""; // Limpiar tabla también
    
                // Filtrar docentes únicos por matrícula
                const docentesUnicos = {};
                data.forEach(doc => {
                    if (!docentesUnicos[doc.matricula_docente]) {
                        docentesUnicos[doc.matricula_docente] = doc.nombre;
                    }
                });
    
                for (const matricula in docentesUnicos) {
                    contenedorSelect.innerHTML += `<option value="${matricula}">${docentesUnicos[matricula]}</option>`;
                }
    
                // Al seleccionar docente
                contenedorSelect.addEventListener("change", function () {
                    const matriculaDocente = this.value;
                    if (!matriculaDocente) return;
    
                    // Filtrar materias solo del docente seleccionado
                    const materiasDocente = data.filter(d => d.matricula_docente === matriculaDocente);
    
                    tablaDocentes.innerHTML = ""; // Limpiar
    
                    if (materiasDocente.length === 0) {
                        tablaDocentes.innerHTML = `<tr><td colspan="4">Este docente no tiene materias registradas.</td></tr>`;
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

        // GRUPAL
        periodoGrupo.addEventListener("change", function () {
            const periodoId = this.value;
            if (!periodoId) return;
        
            fetch(`../Funcionamiento/obtener_grupos.php?periodo_id=${periodoId}`)
                .then(res => res.json())
                .then(grupos => {
                    grupoSelect.innerHTML = '<option value="">Selecciona un grupo</option>';
                    grupos.forEach(grupo => {
                        grupoSelect.innerHTML += `<option value="${grupo.id}">${grupo.nombre}</option>`;
                    });
                })
                .catch(error => console.error("Error cargando grupos:", error));
        });
        
        // Mostrar botón al seleccionar grupo
        grupoSelect.addEventListener("change", function () {
            const grupoId = this.value;
            if (generarGrupoBtn) generarGrupoBtn.style.display = grupoId ? "inline-block" : "none";

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
        
    // Botón de generación de PDF por grupo
        if (e.target.id === "generar-reporte-grupo") {
            const periodoId = periodoGrupo.value;
            const grupoId = grupoSelect.value;

            if (!periodoId || !grupoId) {
                alert("Por favor, selecciona un período y un grupo.");
                return;
            }

            const url = `../Funcionamiento/pdf/reporte_grupal.php?grupo_id=${grupoId}&periodo_id=${periodoId}`;
            window.open(url, "_blank");
        }
    }); 
}); 
});