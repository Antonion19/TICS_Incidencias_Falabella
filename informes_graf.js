// ====================================================================
// Cargar datos desde get_graficos.php
// ====================================================================
fetch("get_graficos.php")
    .then(response => response.json())
    .then(data => {

        // ================================================================
        // 1. GRÁFICO DE BARRAS - Incidentes por mes
        // ================================================================
        new Chart(document.getElementById("chartMeses"), {
            type: "bar",
            data: {
                labels: data.meses,
                datasets: [
                    {
                        label: "Pendientes",
                        data: data.pendientes,
                        backgroundColor: "#ffb300"
                    },
                    {
                        label: "Resueltos",
                        data: data.resueltos,
                        backgroundColor: "#1abc9c"
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                datalabels: {
                    color: '#000',
                    anchor: 'end',
                    align: 'start',
                    formatter: value => value,  // puedes formatear si quieres
                    font: { size: 12, weight: 'bold' }
                }
                },
                scales: {
                y: { beginAtZero: true, precision: 0 }
                }
            },
            plugins: [ChartDataLabels]
        });

        // ================================================================
        // 2. GRÁFICO DE PIE - Categorías
        // ================================================================
        new Chart(document.getElementById("chartCategorias"), {
            type: "pie",
            data: {
                labels: data.categorias_labels,
                datasets: [{
                    data: data.categorias_data,
                    backgroundColor: [
                        "#e74c3c", // hardware
                        "#3498db", // software
                        "#f39c12", // red
                        "#2ecc71", // acceso
                        "#9b59b6"  // otros
                    ]
                }]
            },
            options: { 
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    datalabels: {
                        color: '#fff',
                        formatter: value => value,
                        font: { size: 12, weight: 'bold' }
                    },
                    legend: { position: "bottom" }
                }
             },
             plugins: [ChartDataLabels]
        });

        // ================================================================
        // 3. GRÁFICO DONUT - Estados
        // ================================================================
        new Chart(document.getElementById("chartEstados"), {
            type: "doughnut",
            data: {
                labels: data.estados_labels,
                datasets: [{
                    data: data.estados_data,
                    backgroundColor: [
                        "#27ae60", // cerrado
                        "#2980b9", // en proceso
                        "#c0392b"  // abierto
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    datalabels: {
                        color: '#fff',
                        formatter: value => value,
                        font: { size: 12, weight: 'bold' }
                    },
                    legend: { position: "bottom" }
                }
            },
            plugins: [ChartDataLabels]
        });

        // ================================================================
        // 4. GRÁFICO HORIZONTAL - Prioridades
        // ================================================================
        new Chart(document.getElementById("chartPrioridades"), {
            type: "bar",
            data: {
                labels: data.prioridades_labels,
                datasets: [{
                    label: "Cantidad",
                    data: data.prioridades_data,
                    backgroundColor: [
                        "#e74c3c", // Alta
                        "#f39c12", // Media
                        "#3498db", // Baja
                        "#000000"  // Urgente
                    ]
                }]
            },
            options: {
                indexAxis: "y",
                responsive: true,
                scales: {
                    x: { beginAtZero: true }
                },
                plugins: {
                datalabels: {
                    color: '#fff',
                    anchor: 'end',
                    align: 'start',
                    formatter: value => value,
                    font: { size: 12, weight: 'bold' }
                }
                }
            },
            plugins: [ChartDataLabels]
        });

    })
    .catch(err => console.error("Error cargando gráficos:", err));
