document.addEventListener("DOMContentLoaded", function() {
    const toggleSidebar = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");
    const content = document.getElementById("content");
    const periodoSelect = document.getElementById("periodo");
    const materiaSelect = document.getElementById("materiaGrupo");
    const numInscritos = document.getElementById("numInscritos");
    const numAprobados = document.getElementById("numAprobados");
    const numRiesgo = document.getElementById("numRiesgo");
    const numReprobados = document.getElementById("numReprobados");
    const promedioGeneral = document.getElementById("promedioGeneral");
    const chartCanvas = document.getElementById("graficoRendimiento").getContext("2d");
    let myChart;

    toggleSidebar.addEventListener("click", function() {
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

    periodoSelect.addEventListener("change", function () {
        const periodo_id = this.value;
        if (!periodo_id) return;

        fetch("../Funcionamiento/obtener_materias.php", {
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

    function cargarEstadisticas() {
        const periodoId = periodoSelect.value;
        const materiaId = materiaSelect.value;

        if (periodoId && materiaId) {
            fetch("../Funcionamiento/obtener_estadisticas.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `periodo_id=${encodeURIComponent(periodoId)}&materia_id=${encodeURIComponent(materiaId)}`
            })
            .then(response => response.json())
            .then(data => {
                numInscritos.innerText = data.inscritos;
                numAprobados.innerText = data.aprobados;
                numRiesgo.innerText = data.en_riesgo;
                numReprobados.innerText = data.reprobados;
                promedioGeneral.innerText = data.promedio;
                
                if (myChart) {
                    myChart.destroy();
                }
                
                myChart = new Chart(chartCanvas, {
                    type: 'bar',
                    data: {
                        labels: ["Parcial 1", "Parcial 2", "Parcial 3"],
                        datasets: [{
                            label: 'Promedio de Calificaciones',
                            data: data.promedios_parciales,
                            backgroundColor: 'rgba(54, 162, 235, 0.5)'
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: { beginAtZero: true, max: 10 }
                        }
                    }
                });
            })
            .catch(error => console.error("Error cargando estadísticas:", error));
        }
    }

    periodoSelect.addEventListener("change", cargarEstadisticas);
    materiaSelect.addEventListener("change", cargarEstadisticas);
});