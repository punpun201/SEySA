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

    fetch("../Funcionamiento/obtener_periodos.php")
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

    // DOCENTES
    if (usuarioTipo === "docente") {
        periodoSelect.addEventListener("change", function () {
            const periodo_id = this.value;
            if (!periodo_id) return;

            fetch("../Funcionamiento/obtener_materias_profesor.php", {
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

        // Obtener calificaciones 
        function cargarAlumnosYCalificaciones() {
            const materia_id = materiaSelect.value;
            const periodo_id = periodoSelect.value;
            if (!materia_id || !periodo_id) return;
        
            fetch("../Funcionamiento/obtener_calificaciones_docente.php", {
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
                            <td><select class="form-select calificacion">${generarOpcionesCalificacion(alumno.parcial_1)}</select></td>
                            <td><select class="form-select calificacion">${generarOpcionesCalificacion(alumno.parcial_2)}</select></td>
                            <td><select class="form-select calificacion">${generarOpcionesCalificacion(alumno.parcial_3)}</select></td>
                            <td>${alumno.calificacion_final ?? 'Pendiente'}</td>
                        </tr>`;
                });                
            })
            .catch(error => console.error("Error cargando alumnos y calificaciones:", error));
        }

        materiaSelect.addEventListener("change", cargarAlumnosYCalificaciones);

        document.getElementById("guardarTodasCalificaciones").addEventListener("click", function () {
            const filas = document.querySelectorAll("#tablaDocente tr");
            let datos = [];
        
            filas.forEach(fila => {
                const alumno_id = fila.getAttribute("data-id");
                const parciales = fila.querySelectorAll(".calificacion");
        
                const parcial_1 = parciales[0].value || null;
                const parcial_2 = parciales[1].value || null;
                const parcial_3 = parciales[2].value || null;
        
                datos.push({
                    alumno_id,
                    parcial_1,
                    parcial_2,
                    parcial_3
                });
            });
        
            const materia_id = materiaSelect.value;
            const periodo_id = periodoSelect.value;
        
            fetch("../Funcionamiento/guardar_calificaciones.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ materia_id, periodo_id, calificaciones: datos })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let modal = new bootstrap.Modal(document.getElementById('modalCalificacionGuardada'));
                    modal.show();
        
                    // 🔹 **Calcular calificación final para cada alumno**
                    datos.forEach(alumno => {
                        calcularCalificacionFinal(alumno.alumno_id, materia_id, periodo_id);
                    });
        
                    // 🔹 **Recargar las calificaciones**
                    cargarAlumnosYCalificaciones();
                } else {
                    alert("Error al guardar las calificaciones: " + data.message);
                }
            })
            .catch(error => console.error("Error al guardar calificaciones:", error));
        });

        function generarNotificaciones(alumno_id) {
            fetch("../Funcionamiento/generar_notificaciones.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `alumno_id=${alumno_id}`
            })
            .then(response => response.json())
            .then(data => {
                console.log("Notificaciones generadas:", data);
                if (!data.success) {
                    console.warn("Error al generar notificaciones:", data.error);
                }
            })
            .catch(error => console.error("Error al generar notificaciones:", error));
        }
       
        // Función para calcular la calificación final
        function calcularCalificacionFinal(alumno_id, materia_id, periodo_id) {
            fetch("../Funcionamiento/calificacion_final.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `alumno_id=${alumno_id}&materia_id=${materia_id}&periodo_id=${periodo_id}`
            })
            .then(response => response.json())
            .then(data => {
                console.log("Respuesta de calificación final:", data);
                if (data.success) {
                    const fila = document.querySelector(`tr[data-id='${alumno_id}']`);
                    if (fila) {
                        fila.querySelector("td:nth-child(5)").textContent = data.calificacion_final;
                    }
                    // 🔹 **Ejecutar la generación de notificaciones SOLO después de actualizar la calificación final**
                    return generarNotificaciones(alumno_id);
                } else {
                    console.warn("Error al calcular calificación final:", data.error);
                }
            })
            .catch(error => console.error("Error al calcular calificación final:", error));
        }                        
    }
     
    document.addEventListener("DOMContentLoaded", function () {
        let comentarioAlumnoId = null;
    
        // Evento para abrir el modal y cargar el comentario del alumno
        document.addEventListener("click", function (event) {
            if (event.target.classList.contains("comentario-alumno")) {
                comentarioAlumnoId = event.target.getAttribute("data-id");
                const nombreAlumno = event.target.getAttribute("data-nombre");
                document.getElementById("comentarioAlumnoNombre").textContent = nombreAlumno;
    
                // Obtener el comentario guardado
                fetch("Funcionamiento/obtener_comentario.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `alumno_id=${comentarioAlumnoId}`
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById("comentarioTexto").value = data.comentario || "";
                    new bootstrap.Modal(document.getElementById("modalComentarios")).show();
                })
                .catch(error => console.error("Error al obtener comentario:", error));
            }
        });
    
        // Guardar comentario en la base de datos
        document.getElementById("guardarComentario").addEventListener("click", function () {
            const comentarioTexto = document.getElementById("comentarioTexto").value;
    
            fetch("../Funcionamiento/guardar_comentario.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `alumno_id=${comentarioAlumnoId}&comentario=${encodeURIComponent(comentarioTexto)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Comentario guardado correctamente");
                    bootstrap.Modal.getInstance(document.getElementById("modalComentarios")).hide();
                } else {
                    alert("Error al guardar comentario");
                }
            })
            .catch(error => console.error("Error al guardar comentario:", error));
        });
    });
    
    // ALUMNOS
    if (usuarioTipo === "alumno") {
        periodoSelect.addEventListener("change", function () {
            const periodo_id = this.value;
            if (!periodo_id) return;
    
            fetch("../Funcionamiento/obtener_calificaciones_alumno.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `periodo_id=${encodeURIComponent(periodo_id)}`
            })
            .then(response => response.json())
            .then(data => {
                console.log("Respuesta del servidor:", data); 
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