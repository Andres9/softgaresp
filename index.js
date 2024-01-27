$(document).ready(function () {
    $('#myTable').DataTable({
        "language": {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
            responsive: true
        }
    });

    $('.select').select2();

    $('.fecha').hide();

    $('.datepicker').pickadate({
        monthsFull: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
        monthsShort: ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'],
        weekdaysFull: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
        weekdaysShort: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
        today: 'Hoy',
        clear: 'Limpiar',
        close: 'Cerrar',
        labelMonthNext: 'Siguiente mes',
        labelMonthPrev: 'Mes anterior',
        labelMonthSelect: 'Seleccione un mes',
        labelYearSelect: 'Seleccione un año',
        firstDay: 1,
        format: 'dddd, dd !de mmmm !de yyyy',
        formatSubmit: 'dd/mm/yyyy',
        selectYears: true,
        selectMonths: true,
        min: true,
        max: 30,
        onStart: function () {
            var date = new Date();
            this.set('select', [date.getFullYear(), date.getMonth(), date.getDate()]);
        }
    });


});

