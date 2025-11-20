// ===============================
//   CONFIGURACIÓN INICIAL
// ===============================

// Este valor lo reemplazaremos con PHP más adelante
const usuarioActualID = window.USUARIO_ACTUAL_ID || null;

// Inicializar DataTable
let tabla = null;

$(document).ready(function () {

    tabla = $("#tablaGestionInc").DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
        },
        columnDefs: [
            { targets: [0], width: "40px" },
            { targets: [10], width: "80px" },   // Columna Repositorio
            { targets: [11], width: "160px" }   // Columna Acciones
        ]
    });

    // ===============================
    //   FILTROS SUPERIORES
    // ===============================

    $(".filtro-btn").on("click", function () {

        const filtro = $(this).data("filter");

        tabla.columns().search(""); // limpiar filtros previos

        switch (filtro) {

            case "asignados":
                // El backend deberá colocar ID en la columna "Asignado A"
                tabla.column(7).search(usuarioActualID);
                break;

            case "abiertos":
                tabla.column(5).search("Abierto");
                break;

            case "progreso":
                tabla.column(5).search("En proceso");
                break;

            case "resueltos":
                tabla.column(5).search("Cerrado");
                break;

            default:
                tabla.search("");
        }

        tabla.draw();
    });

});


// ===============================
//   GENERADOR DE BOTONES
// ===============================

// rowData = datos completos de la fila
function generarAcciones(rowData) {

    const asignadoA = rowData[7]; 
    const estado = rowData[5];
    const repo = rowData[10];

    let botones = "";

    // Caso 1: No asignado → Mostrar botón "Asignarme"
    if (!asignadoA || asignadoA.trim() === "" || asignadoA === "No asignado") {
        botones += `
            <button class="btn btn-sm btn-outline-primary btn-asignar" data-id="${rowData[0]}">
                <i class="fa-solid fa-user-check"></i> Asignarme
            </button>`;
        return botones;
    }

    // Caso 2: Está asignado a otro → solo ver solución
    if (asignadoA != usuarioActualID) {
        botones += `
            <button class="btn btn-sm btn-outline-dark btn-ver" data-id="${rowData[0]}">
                <i class="fa-solid fa-eye"></i> Ver
            </button>`;
        return botones;
    }

    // Caso 3: Está asignado a mí → acciones completas
    if (asignadoA == usuarioActualID) {

        // --- Registrar solución (solo si no está cerrado)
        if (estado !== "Cerrado") {
            botones += `
                <button class="btn btn-sm btn-success btn-solucion" data-id="${rowData[0]}">
                    <i class="fa-solid fa-pen"></i> Registrar Solución
                </button>`;
        }

        // --- Liberar incidencia (solo si no está cerrado)
        if (estado !== "Cerrado") {
            botones += `
                <button class="btn btn-sm btn-warning btn-liberar" data-id="${rowData[0]}">
                    <i class="fa-solid fa-share"></i> Liberar
                </button>`;
        }

        // --- Agregar al repositorio (solo si está cerrado y NO está ya agregado)
        if (estado === "Cerrado" && repo === "No") {
            botones += `
                <button class="btn btn-sm btn-info btn-repo" data-id="${rowData[0]}">
                    <i class="fa-solid fa-book"></i> Repo
                </button>`;
        }

        // --- Ver solución siempre disponible
        botones += `
            <button class="btn btn-sm btn-outline-dark btn-ver" data-id="${rowData[0]}">
                <i class="fa-solid fa-eye"></i>
            </button>`;
    }

    return botones;
}


// ===============================
//   EVENTOS DE BOTONES
// ===============================

// Aún no implementamos lógica PHP, solo estructuras de captura

$(document).on("click", ".btn-asignar", function () {
    const id = $(this).data("id");
    console.log("Asignar incidente:", id);
});

$(document).on("click", ".btn-solucion", function () {
    const id = $(this).data("id");
    console.log("Registrar solución:", id);
});

$(document).on("click", ".btn-liberar", function () {
    const id = $(this).data("id");
    console.log("Liberar incidente:", id);
});

$(document).on("click", ".btn-repo", function () {
    const id = $(this).data("id");
    console.log("Agregar a repositorio:", id);
});

$(document).on("click", ".btn-ver", function () {
    const id = $(this).data("id");
    console.log("Ver solución:", id);
});
