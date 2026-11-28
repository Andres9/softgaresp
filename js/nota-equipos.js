(function () {
    'use strict';
    const lista = document.querySelector('.nota-equipos-lista');
    const agregar = document.querySelector('.nota-equipo-agregar');
    if (!lista || !agregar) return;
    const plantilla = lista.firstElementChild.cloneNode(true);

    function numerar() {
        lista.querySelectorAll('.nota-equipo-linea').forEach(function (linea, indice) {
            linea.querySelector('.nota-equipo-numero').textContent = indice + 1;
        });
    }

    function limpiar(linea) {
        linea.querySelectorAll('input').forEach(function (campo) { campo.value = ''; });
        const selector = linea.querySelector('select');
        if (window.jQuery) {
            window.jQuery(selector).val('').trigger('change');
        } else {
            selector.value = '';
        }
    }

    agregar.addEventListener('click', function () {
        if (lista.children.length >= 20) {
            alert('La nota admite hasta 20 equipos.');
            return;
        }
        const linea = plantilla.cloneNode(true);
        linea.querySelectorAll('input').forEach(function (campo) { campo.value = ''; });
        linea.querySelector('select').value = '';
        lista.appendChild(linea);
        if (window.inicializarSelectores) window.inicializarSelectores(linea.querySelector('select'));
        numerar();
        linea.querySelector('select').focus();
    });
    lista.addEventListener('click', function (evento) {
        if (!evento.target.closest('.nota-equipo-quitar')) return;
        const linea = evento.target.closest('.nota-equipo-linea');
        if (lista.children.length > 1) {
            linea.remove();
            lista.dispatchEvent(new Event('change', { bubbles: true }));
        } else {
            limpiar(linea);
        }
        numerar();
    });
    numerar();
}());
