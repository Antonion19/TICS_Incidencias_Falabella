// ===============================
// GRÁFICO DE BARRAS - Incidentes por mes
// ===============================
new Chart(document.getElementById("chartMeses"), {
    type: "bar",
    data: {
        labels: ["May", "Jun", "Jul", "Ago", "Sep", "Oct"],
        datasets: [
            {
                label: "Pendientes",
                data: [8, 6, 7, 9, 5, 8],
                backgroundColor: "#ffb300"
            },
            {
                label: "Resueltos",
                data: [35, 44, 38, 50, 47, 33],
                backgroundColor: "#1abc9c"
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: true }
        }
    }
});

// ===============================
// GRÁFICO DE PIE - Categorías
// ===============================
new Chart(document.getElementById("chartCategorias"), {
    type: "pie",
    data: {
        labels: ["Hardware", "Software", "Red", "Acceso", "Otros"],
        datasets: [{
            data: [29, 24, 18, 14, 15],
            backgroundColor: ["#e74c3c","#3498db","#f39c12","#2ecc71","#9b59b6"]
        }]
    },
    options: { responsive: true }
});