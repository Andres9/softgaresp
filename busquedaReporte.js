
function buscar_filtro(tabla) {
    var parametros = {"tabla": tabla };

    $.ajax({
        data: parametros,
        url: 'consultaReporte.php',
        type: 'POST',
        timeout: 10000,
        success: function (response) {
            document.getElementById("resultado_busqueda").innerHTML = response;
        },

        error: function (response, error) {
            document.getElementById("resultado_busqueda").innerHTML = error;
        }
    });
}





