document.addEventListener("DOMContentLoaded", function () {
    const toggleSidebar = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");
    const content = document.getElementById("content");
    const periodoSelect = document.getElementById("periodo");
    const materiaSelect = document.getElementById("materiaGrupo");
    const tablaDocente = document.getElementById("tablaDocente");
    const tablaAlumno = document.getElementById("tablaAlumno");
    const usuarioTipo = document.body.getAttribute("data-usuario"); 

    // Manejo del sidebar
    toggleSidebar.addEventListener("click", function () {
        sidebar.classList.toggle("collapsed");
        content.classList.toggle("collapsed");
    });

    // Carga los períodos automáticamente
    fetch("Funcionamiento/obtener_periodos.php")
        .then(response => response.json())
        .then(data => {
            periodoSelect.innerHTML = '<option value="">Selecciona un período</option>';
            data.forEach(periodo => {
                periodoSelect.innerHTML += `<option value="${periodo.id}">${periodo.nombre}</option>`;
            });
        })
        .catch(error => console.error("Error cargando períodos:", error));

    // Generar opciones de calificación (1 a 10)
    function generarOpcionesCalificacion(seleccionado) {
        let options = `<option value="">Selecciona</option>`;
        for (let i = 1; i <= 10; i++) {
            let selected = (seleccionado == i) ? "selected" : "";
            options += `<option value="${i}" ${selected}>${i}</option>`;
        }
        return options;
    }

    // **DOCENTE**
    if (usuarioTipo === "docente") {
        periodoSelect.addEventListener("change", function () {
            const periodo_id = this.value;
            if (!periodo_id) return;

            fetch("Funcionamiento/obtener_materias_profesor.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `periodo_id=${encodeURIComponent(periodo_id)}`
            })
            .then(response => response.json())
            .then(data => {
                materiaSelect.innerHTML = '<option value="">Selecciona una materia</option>';
                if (data.error) {
                    alert("No hay materias asignadas en este período.");
                    return;
                }
                data.forEach(materia => {
                    materiaSelect.innerHTML += `<option value="${materia.id}">${materia.nombre}</option>`;
                });
            })
            .catch(error => console.error("Error cargando materias:", error));
        });

        function cargarAlumnosYCalificaciones() {
            const materia_id = materiaSelect.value;
            const periodo_id = periodoSelect.value;
            if (!materia_id || !periodo_id) return;

            fetch("Funcionamiento/obtener_calificaciones_docente.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `materia_id=${materia_id}&periodo_id=${periodo_id}`
            })
            .then(response => response.json())
            .then(alumnos => {
                tablaDocente.innerHTML = ""; 

                if (alumnos.length === 0) {
                    tablaDocente.innerHTML = `<tr><td colspan="6" class="text-center">No hay alumnos inscritos en esta materia</td></tr>`;
                    return;
                }

                alumnos.forEach(alumno => {
                    tablaDocente.innerHTML += `
                        <tr data-id="${alumno.id}">
                            <td>${alumno.nombre}</td>
                            <td><select class="form-select calificacion">${generarOpcionesCalificacion(alumno.parcial_1)}</select></td>
                            <td><select class="form-select calificacion">${generarOpcionesCalificacion(alumno.parcial_2)}</select></td>
                            <td><select class="form-select calificacion">${generarOpcionesCalificacion(alumno.parcial_3)}</select></td>
                            <td>${alumno.calificacion_final ?? 'Pendiente'}</td>
                            <td>
                                <button class="btn btn-sm btn-success guardar-calificacion">
                                    <i class="fas fa-save"></i> Guardar
                                </button>
                                <button class="btn btn-sm btn-warning editar-calificacion">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                            </td>
                        </tr>
                    `;
                });
            })
            .catch(error => console.error("Error cargando alumnos y calificaciones:", error));
        }

        // Cuando el docente selecciona una materia, cargar los alumnos
        materiaSelect.addEventListener("change", function () {
            const materia_id = this.value;
            const periodo_id = periodoSelect.value;
            if (!materia_id || !periodo_id) return;

            fetch("Funcionamiento/obtener_alumnos.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `materia_id=${materia_id}&periodo_id=${periodo_id}`
            })
            .then(response => response.json())
            .then(alumnos => {
                tablaDocente.innerHTML = ""; // Limpiar tabla antes de llenarla

                if (alumnos.length === 0) {
                    tablaDocente.innerHTML = `<tr><td colspan="6" class="text-center">No hay alumnos inscritos en esta materia</td></tr>`;
                    return;
                }

                alumnos.forEach(alumno => {
                    tablaDocente.innerHTML += `
                        <tr data-id="${alumno.id}">
                            <td>${alumno.nombre}</td>
                            <td><select class="form-select calificacion">${generarOpcionesCalificacion(alumno.parcial_1)}</select></td>
                            <td><select class="form-select calificacion">${generarOpcionesCalificacion(alumno.parcial_2)}</select></td>
                            <td><select class="form-select calificacion">${generarOpcionesCalificacion(alumno.parcial_3)}</select></td>
                            <td>${alumno.calificacion_final ?? 'Pendiente'}</td>
                            <td>
                                <button class="btn btn-sm btn-success guardar-calificacion">
                                    <i class="fas fa-save"></i> Guardar
                                </button>
                                <button class="btn btn-sm btn-warning editar-calificacion">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                            </td>
                        </tr>
                    `;
                });
            })
            .catch(error => console.error("Error cargando alumnos:", error));
        });

        // Evento de guardar calificaciones
        document.addEventListener("click", function (event) {
            if (event.target.classList.contains("guardar-calificacion")) {
                const fila = event.target.closest("tr");
                const alumno_id = fila.getAttribute("data-id");
                const parciales = fila.querySelectorAll(".calificacion");
        
                const parcial_1 = parciales[0].value || null;
                const parcial_2 = parciales[1].value || null;
                const parcial_3 = parciales[2].value || null;
                const materia_id = materiaSelect.value;
                const periodo_id = periodoSelect.value;
        
                console.log("Enviando datos:", { alumno_id, materia_id, periodo_id, parcial_1, parcial_2, parcial_3 });
        
                fetch("Funcionamiento/guardar_calificaciones.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `alumno_id=${alumno_id}&materia_id=${materia_id}&periodo_id=${periodo_id}&parcial_1=${parcial_1}&parcial_2=${parcial_2}&parcial_3=${parcial_3}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Calificación guardada correctamente");
                    } else {
                        alert("Error al guardar la calificación: " + data.message);
                    }
                })
                .catch(error => console.error("Error al guardar calificación:", error));
            }
        });
    }        

    // **ALUMNO**
    if (usuarioTipo === "alumno") {
        periodoSelect.addEventListener("change", function () {
            const periodo_id = this.value;
            if (!periodo_id) return;

            fetch("Funcionamiento/obtener_calificaciones_alumno.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `periodo_id=${encodeURIComponent(periodo_id)}`
            })
            .then(response => response.json())
            .then(data => {
                tablaAlumno.innerHTML = ""; 

                if (data.length === 0) {
                    tablaAlumno.innerHTML = `<tr><td colspan="5" class="text-center">No hay calificaciones registradas.</td></tr>`;
                    return;
                }

                data.forEach(calificacion => {
                    tablaAlumno.innerHTML += `
                        <tr>
                            <td>${calificacion.materia}</td>
                            <td>${calificacion.parcial_1 ?? 'Pendiente'}</td>
                            <td>${calificacion.parcial_2 ?? 'Pendiente'}</td>
                            <td>${calificacion.parcial_3 ?? 'Pendiente'}</td>
                            <td>${calificacion.calificacion_final ?? 'Pendiente'}</td>
                        </tr>
                    `;
                });
            })
            .catch(error => console.error("Error cargando calificaciones del alumno:", error));
        });
    }
});