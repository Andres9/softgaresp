
function buscar_filtro(tabla) {
    var buscar ='1';
    var parametros = { "buscar": buscar, "tabla": tabla };

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





