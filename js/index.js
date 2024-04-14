$(document).ready(function () {

    $('#tablaclientes,#tablaventas,#myTable,#myTable2').DataTable({
        "language": {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
        },
        responsive: true
    });

    $('.select').select2();

    $('.fecha').hide();

});

