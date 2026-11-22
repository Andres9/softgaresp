(function () {
    'use strict';

    if (!window.jQuery || !window.jQuery.fn.select2) {
        console.error('No se pudo cargar el complemento de selección; se usarán los controles originales.');
        return;
    }

    window.inicializarSelectores = function (elementos) {
        window.jQuery(elementos).filter(':not(.select2-hidden-accessible)').select2({
            width: '100%',
            selectionCssClass: 'selector-datos-seleccion',
            dropdownCssClass: 'selector-datos-opciones',
            language: {
                noResults: function () { return 'No se encontraron resultados'; },
                searching: function () { return 'Buscando...'; }
            }
        });
    };

    window.jQuery(function ($) {
        window.inicializarSelectores($('select.select'));
    });
}());
