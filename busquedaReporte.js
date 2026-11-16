
function buscar_filtro(tabla) {
    var $resultado = $("#resultado_busqueda");

    if (tabla !== "notas" && tabla !== "ventas") {
        $resultado.empty();
        return;
    }

    $resultado.html('<p class="consultas-status">Cargando información...</p>');
    $.ajax({
        data: { tabla: tabla },
        url: 'consultaReporte.php',
        type: 'POST',
        dataType: 'html',
        timeout: 10000,
        success: function (response) {
            $resultado.html(response);

            var $table = $resultado.find("table").first();
            if ($table.length) {
                $table.DataTable({
                    responsive: true,
                    language: {
                        emptyTable: "Ningún dato disponible en esta tabla",
                        info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                        infoEmpty: "Mostrando 0 a 0 de 0 registros",
                        infoFiltered: "(filtrado de _MAX_ registros)",
                        lengthMenu: "Mostrar _MENU_ registros",
                        loadingRecords: "Cargando...",
                        search: "Buscar:",
                        zeroRecords: "No se encontraron resultados",
                        paginate: {
                            first: "Primero",
                            last: "Último",
                            next: "Siguiente",
                            previous: "Anterior"
                        }
                    }
                });
            }
        },

        error: function (xhr, status) {
            var mensaje = status === "timeout"
                ? "La consulta tardó demasiado. Intenta de nuevo."
                : "No fue posible cargar la información. Intenta de nuevo.";
            $resultado.html('<p class="consultas-status" role="alert"></p>');
            $resultado.find(".consultas-status").text(mensaje);
        }
    });
}

$(function () {
    $("#tabla").on("change", function () {
        buscar_filtro(this.value);
    });

    $("#form_consultas").on("submit", function (event) {
        event.preventDefault();
        buscar_filtro($("#tabla").val());
    });
});
