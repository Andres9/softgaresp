
function buscar_filtro(tabla) {
    var buscar ='1';
    var parametros = { "buscar": buscar, "tabla": tabla };

    $.ajax({
        data: parametros,
        url: 'consultaReporte.php',
        type: 'POST',
        timeout: 10000,
        beforeSend: function(){

        },
        success: function (response) {
            console.log("DENTRO");
            document.getElementById("resultado_busqueda").innerHTML = response;
            
        },

        error: function (response, error) {
            console.log("Error");
            document.getElementById("resultado_busqueda").innerHTML = error;

        }
    });
}





