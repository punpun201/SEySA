document.addEventListener("DOMContentLoaded", function () {
    const toggleSidebar = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");
    const content = document.getElementById("content");
    const periodoSelect = document.getElementById("periodo");
    const materiaSelect = document.getElementById("materiaGrupo");
    const tablaDocente = document.getElementById("tablaDocente");
    const tablaAlumno = document.getElementById("tablaAlumno");
    const usuarioTipo = document.body.getAttribute("data-usuario"); // Obtener tipo de usuario

    // Manejo del sidebar
    toggleSidebar.addEventListener("click", function () {
        sidebar.classList.toggle("collapsed");
        content.classList.toggle("collapsed");
    });

    // Carga los períodos automáticamente al entrar a la página
    fetch("Funcionamiento/obtener_periodos.php")
        .then(response => response.json())
        .then(data => {
            periodoSelect.innerHTML = '<option value="">Selecciona un período</option>';
            data.forEach(periodo => {
                periodoSelect.innerHTML += `<option value="${periodo.id}">${periodo.nombre}</option>`;
            });
        })
        .catch(error => console.error("Error cargando períodos:", error));

    // 🔹 **DOCENTE**
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
                    console.error("Error:", data.error);
                    alert("No hay materias asignadas en este período.");
                    return;
                }
                data.forEach(materia => {
                    materiaSelect.innerHTML += `<option value="${materia.id}">${materia.nombre}</option>`;
                });
            })
            .catch(error => console.error("Error cargando materias:", error));
        });

        // Cuando el docente selecciona una materia, cargar los alumnos inscritos
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
                tablaDocente.innerHTML = ""; // Limpiar la tabla antes de llenarla

                if (alumnos.length === 0) {
                    tablaDocente.innerHTML = `<tr><td colspan="6" class="text-center">No hay alumnos inscritos en esta materia</td></tr>`;
                    return;
                }

                alumnos.forEach(alumno => {
                    tablaDocente.innerHTML += `
                        <tr>
                            <td>${alumno.nombre}</td>
                            <td><input type="number" class="form-control calificacion" data-id="${alumno.id}" value="${alumno.parcial_1 ?? ''}" max="100" min="0"></td>
                            <td><input type="number" class="form-control calificacion" data-id="${alumno.id}" value="${alumno.parcial_2 ?? ''}" max="100" min="0"></td>
                            <td><input type="number" class="form-control calificacion" data-id="${alumno.id}" value="${alumno.parcial_3 ?? ''}" max="100" min="0"></td>
                            <td>${alumno.calificacion_final ?? 'Pendiente'}</td>
                            <td>
                                <button class="btn btn-sm btn-success guardar-calificacion" data-id="${alumno.id}">
                                    <i class="fas fa-save"></i> Guardar
                                </button>
                            </td>
                        </tr>
                    `;
                });
            })
            .catch(error => console.error("Error cargando alumnos:", error));
        });
    }

    // 🔹 **ALUMNO**
    if (usuarioTipo === "alumno") {
        periodoSelect.addEventListener("change", function () {
            const periodo_id = this.value;
            if (!periodo_id) return;

            fetch("Funcionamiento/obtener_materias_alumno.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `periodo_id=${encodeURIComponent(periodo_id)}`
            })
            .then(response => response.json())
            .then(data => {
                materiaSelect.innerHTML = '<option value="">Selecciona una materia</option>';
                if (data.length === 0) {
                    console.warn("No hay materias registradas.");
                    return;
                }
                data.forEach(materia => {
                    materiaSelect.innerHTML += `<option value="${materia.id}">${materia.nombre}</option>`;
                });
            })
            .catch(error => console.error("Error cargando materias del alumno:", error));
        });

        // Cuando el alumno selecciona una materia, mostrar su calificación
        materiaSelect.addEventListener("change", function () {
            const materia_id = this.value;
            const periodo_id = periodoSelect.value;
            if (!materia_id || !periodo_id) return;

            fetch("Funcionamiento/obtener_calificaciones_alumno.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `materia_id=${materia_id}&periodo_id=${periodo_id}`
            })
            .then(response => response.json())
            .then(data => {
                tablaAlumno.innerHTML = ""; // Limpiar tabla antes de agregar nuevas filas

                if (data.length === 0) {
                    tablaAlumno.innerHTML = `<tr><td colspan="5" class="text-center">No hay calificaciones registradas para esta materia.</td></tr>`;
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