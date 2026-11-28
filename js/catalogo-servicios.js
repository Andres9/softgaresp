(function () {
    'use strict';

    var editar = document.getElementById('editar-servicio');
    var eliminar = document.getElementById('eliminar-servicio');
    if (!editar || !eliminar) return;

    editar.addEventListener('show.bs.modal', function (evento) {
        var boton = evento.relatedTarget;
        if (!boton) return;
        var form = editar.querySelector('form');
        var valores = {
            id_servicio: boton.getAttribute('data-id'),
            nombre: boton.getAttribute('data-nombre'),
            precio: boton.getAttribute('data-precio'),
            categoria: boton.getAttribute('data-categoria'),
            descripcion: boton.getAttribute('data-descripcion'),
            duracion_minutos: boton.getAttribute('data-duracion')
        };
        Object.keys(valores).forEach(function (nombre) {
            form.elements[nombre].value = valores[nombre] || '';
        });
        var error = editar.querySelector('.alert');
        if (error) error.remove();
    });

    eliminar.addEventListener('show.bs.modal', function (evento) {
        var boton = evento.relatedTarget;
        var id = boton ? boton.getAttribute('data-id') : '';
        var valido = /^[1-9]\d*$/.test(id || '');
        eliminar.querySelector('[name="id_servicio"]').value = valido ? id : '';
        eliminar.querySelector('#eliminar-servicio-nombre').textContent = valido ? boton.getAttribute('data-nombre') : '';
        eliminar.querySelector('[type="submit"]').disabled = !valido;
    });

    eliminar.addEventListener('hidden.bs.modal', function () {
        eliminar.querySelector('[type="submit"]').disabled = true;
    });

    if (editar.getAttribute('data-reabrir') === '1' && window.bootstrap && window.bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(editar).show();
    }
}());
