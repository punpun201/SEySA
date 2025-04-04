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
                    console.log("Calificaciones guardadas correctamente.");
                    
                    // Generar notificaciones de parciales primero
                    return fetch("../Funcionamiento/generar_alertas_parciales.php");
                } else {
                    throw new Error("Error al guardar las calificaciones: " + data.message);
                }
            })
            .then(response => response.text())
            .then(text => {
                console.log("Respuesta de notificaciones parciales:", text);
        
                // Luego calcular la calificación final de cada alumno
                return Promise.all(datos.map(alumno => 
                    calcularCalificacionFinal(alumno.alumno_id, materia_id, periodo_id)
                ));
            })
            .then(() => {
                console.log("Notificaciones generadas correctamente.");
        
                // Finalmente, mostrar el modal solo cuando todo haya terminado
                let modal = new bootstrap.Modal(document.getElementById('modalCalificacionGuardada'));
                modal.show();

                // Recargar calificaciones después de todo el proceso
                cargarAlumnosYCalificaciones();
            })
            .catch(error => console.error("Error en el proceso:", error));
        });
                
        // Función para calcular la calificación final
        function calcularCalificacionFinal(alumno_id, materia_id, periodo_id) {
            return fetch("../Funcionamiento/calificacion_final.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `alumno_id=${alumno_id}&materia_id=${materia_id}&periodo_id=${periodo_id}`
            })
            .then(response => response.json()) // Convertir a JSON
            .then(data => {
                if (!data.success) {
                    console.error("Error al calcular calificación final:", data.message);
                    throw new Error("Cálculo fallido");
                }

                console.log(`Calificación final calculada para alumno ${alumno_id}: ${data.calificacion_final}`);

                return fetch("../Funcionamiento/generar_notificaciones_final.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `alumno_id=${alumno_id}&materia_id=${materia_id}&periodo_id=${periodo_id}&calificacion_final=${data.calificacion_final}`
                });
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error("Error al generar notificación:", data.message);
                    throw new Error("Notificación fallida");
                }

                console.log("Notificación generada correctamente.");
            })
            .catch(error => console.error("Error en la ejecución:", error));
        }
    }                    
    
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
            
                if (!Array.isArray(data)) {
                    console.error("La respuesta no es un arreglo:", data);
                    tablaAlumno.innerHTML = `<tr><td colspan="5" class="text-center">Error en los datos recibidos.</td></tr>`;
                    return;
                }
            
                if (data.length === 0) {
                    console.warn("No hay calificaciones registradas.");
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
                        </tr>`;
                });
            })            
            .catch(error => console.error("Error cargando calificaciones del alumno:", error));
        });
    }    
});