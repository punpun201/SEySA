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
                            backgroundColor: [
                                'rgba(54, 162, 235, 0.6)',   // Azul - Parcial 1
                                'rgba(255, 206, 86, 0.6)',   // Amarillo - Parcial 2
                                'rgba(255, 99, 132, 0.6)'    // Rojo - Parcial 3
                            ]
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

        // Botón para exportar PDF
        const exportarBtn = document.getElementById("exportarPDF");
        if (exportarBtn) {
            exportarBtn.addEventListener("click", async () => {
                const { jsPDF } = window.jspdf;
                const contenedor = document.getElementById("estadisticas-container");
            
                if (!contenedor) {
                    alert("No se encontró el contenedor a exportar.");
                    return;
                }
            
                // Captura de la gráfica
                const canvas = await html2canvas(contenedor, {
                    backgroundColor: "#ffffff",
                    scale: 2
                });
            
                const imgData = canvas.toDataURL("image/png");
                const pdf = new jsPDF("p", "mm", "a4");
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = pdf.internal.pageSize.getHeight();
                const margin = 15;
            
                // Información para el encabezado
                const materia = materiaSelect.options[materiaSelect.selectedIndex]?.text || "-";
                const periodo = periodoSelect.options[periodoSelect.selectedIndex]?.text || "-";

                // Encabezado principal
                pdf.setFont("Helvetica", "bold");
                pdf.setFontSize(16);
                pdf.text("Estadísticas de Rendimiento Académico", pdfWidth / 2, margin, { align: "center" });

                // Subtítulos: Periodo y materia
                pdf.setFontSize(12);
                pdf.text(`${periodo}`, pdfWidth / 2, margin + 10, { align: "center" });

                pdf.text(`${materia}`, pdfWidth / 2, margin + 18, { align: "center" });

                // Resumen de datos alineado a la izquierda
                const resumenData = [
                    `Inscritos: ${numInscritos.innerText}`,
                    `Aprobados: ${numAprobados.innerText}`,
                    `En Riesgo: ${numRiesgo.innerText}`,
                    `Reprobados: ${numReprobados.innerText}`,
                    `Promedio Global: ${promedioGeneral.innerText}`,
                ];
            
                // Encabezado
                pdf.setFont("Helvetica", "bold");
                pdf.setFontSize(16);
                pdf.text("Estadísticas de Rendimiento Académico", pdfWidth / 2, margin, { align: "center" });
            
                // Subtítulo y datos
                pdf.setFontSize(12);
                pdf.setFont("Helvetica", "");

                // Espaciado: Dejaa más espacio entre encabezado y resumen
                let currentY = margin + 30; 

                resumenData.forEach(line => {
                    pdf.text(line, margin, currentY);
                    currentY += 7;
                });

                // Inserta las gráficas debajo del resumen
                const imgProps = pdf.getImageProperties(imgData);
                const imageHeight = (imgProps.height * (pdfWidth - margin * 2)) / imgProps.width;
            
                if (currentY + imageHeight > pdfHeight - margin) {
                    pdf.addPage();
                    currentY = margin;
                }
            
                pdf.addImage(imgData, "PNG", margin, currentY, pdfWidth - margin * 2, imageHeight);
                pdf.save("estadisticas_rendimiento.pdf");
            });            
        }
    
    periodoSelect.addEventListener("change", cargarEstadisticas);
    materiaSelect.addEventListener("change", cargarEstadisticas);
});