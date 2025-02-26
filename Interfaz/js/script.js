document.addEventListener("DOMContentLoaded", function () {
    const toggleSidebar = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");
    const content = document.getElementById("content");
    const periodoSelect = document.getElementById("periodo");
    const materiaSelect = document.getElementById("materiaGrupo");
    const tablaDocente = document.getElementById("tablaDocente");
    const tablaAlumno = document.getElementById("tablaAlumno");
    const usuarioTipo = document.body.getAttribute("data-usuario"); 

    toggleSidebar.addEventListener("click", function () {
        sidebar.classList.toggle("collapsed");
        content.classList.toggle("collapsed");
    });

    fetch("Funcionamiento/obtener_periodos.php")
        .then(response => response.json())
        .then(data => {
            periodoSelect.innerHTML = '<option value="">Selecciona un período</option>';
            data.forEach(periodo => {
                periodoSelect.innerHTML += `<option value="${periodo.id}">${periodo.nombre}</option>`;
            });
        })
        .catch(error => console.error("Error cargando períodos:", error));

    function generarOpcionesCalificacion(seleccionado) {
        let options = `<option value="">Selecciona</option>`;
        for (let i = 1; i <= 10; i++) {
            let selected = (seleccionado == i) ? "selected" : "";
            options += `<option value="${i}" ${selected}>${i}</option>`;
        }
        return options;
    }

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
            .then(data => {
                tablaDocente.innerHTML = "";
                if (data.length === 0) {
                    tablaDocente.innerHTML = `<tr><td colspan="6" class="text-center">No hay alumnos inscritos en esta materia</td></tr>`;
                    return;
                }
                data.forEach(alumno => {
                    tablaDocente.innerHTML += `
                        <tr data-id="${alumno.id}">
                            <td>${alumno.nombre}</td>
                            <td><select class="form-select calificacion" data-parcial="1">${generarOpcionesCalificacion(alumno.parcial_1)}</select></td>
                            <td><select class="form-select calificacion" data-parcial="2">${generarOpcionesCalificacion(alumno.parcial_2)}</select></td>
                            <td><select class="form-select calificacion" data-parcial="3">${generarOpcionesCalificacion(alumno.parcial_3)}</select></td>
                            <td>${alumno.calificacion_final ?? 'Pendiente'}</td>
                            <td><button class="btn btn-sm btn-success guardar-calificacion"><i class="fas fa-save"></i> Guardar</button></td>
                        </tr>`;
                });
            })
            .catch(error => console.error("Error cargando alumnos y calificaciones:", error));
        }

        materiaSelect.addEventListener("change", cargarAlumnosYCalificaciones);

        document.addEventListener("click", function (event) {
            if (event.target.classList.contains("guardar-calificacion")) {
                const fila = event.target.closest("tr");
                const alumno_id = fila.getAttribute("data-id");
                const parciales = fila.querySelectorAll(".calificacion");
        
                const parcial_1 = parciales[0].value || parciales[0].getAttribute("data-valor") || null;
                const parcial_2 = parciales[1].value || parciales[1].getAttribute("data-valor") || null;
                const parcial_3 = parciales[2].value || parciales[2].getAttribute("data-valor") || null;
                const materia_id = materiaSelect.value;
                const periodo_id = periodoSelect.value;
        
                fetch("Funcionamiento/guardar_calificaciones.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `alumno_id=${alumno_id}&materia_id=${materia_id}&periodo_id=${periodo_id}&parcial_1=${parcial_1}&parcial_2=${parcial_2}&parcial_3=${parcial_3}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let modal = new bootstrap.Modal(document.getElementById('modalCalificacionGuardada'));
                        modal.show();
                        cargarAlumnosYCalificaciones();
                    } else {
                        alert("Error al guardar la calificación: " + data.message);
                    }
                })
                .catch(error => console.error("Error al guardar calificación:", error));
            }
        });
    }

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
                console.log("Respuesta del servidor:", data); // <-- Muestra los datos en la consola
                tablaAlumno.innerHTML = ""; 
    
                if (data.length === 0) {
                    console.warn("No hay calificaciones registradas.");
                    tablaAlumno.innerHTML = `<tr><td colspan="5" class="text-center">No hay calificaciones registradas.</td></tr>`;
                    return;
                }
    
                data.forEach(calificacion => {
                    console.log(`Procesando: ${calificacion.materia} - P1: ${calificacion.parcial_1}, P2: ${calificacion.parcial_2}, P3: ${calificacion.parcial_3}`);
    
                    tablaAlumno.innerHTML += `
                        <tr>
                            <td>${calificacion.materia}</td>
                            <td>${calificacion.parcial_1 ?? 'Pendiente'}</td>
                            <td>${calificacion.parcial_2 ?? 'Pendiente'}</td>
                            <td>${calificacion.parcial_3 ?? 'Pendiente'}</td>
                            <td>${calificacion.calificacion_final ?? 'Pendiente'}</td>
                        </tr>`;
                });
            })
            .catch(error => console.error("Error cargando calificaciones del alumno:", error));
        });
    }    
});