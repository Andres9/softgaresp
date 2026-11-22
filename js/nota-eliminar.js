(function () {
    'use strict';

    var modal = document.getElementById('confirmar-eliminar-nota');
    if (!modal) return;

    var folio = document.getElementById('confirmar-eliminar-nota-folio');
    var confirmar = document.getElementById('confirmar-eliminar-nota-accion');
    var formulario = null;

    modal.addEventListener('show.bs.modal', function (evento) {
        var boton = evento.relatedTarget;
        var seleccionado = boton && boton.closest('form.nota-eliminar');
        var id = seleccionado && seleccionado.querySelector('[name="id_nota"]');
        formulario = id && /^[1-9]\d*$/.test(id.value) ? seleccionado : null;
        folio.textContent = formulario ? '#' + id.value : '';
        confirmar.disabled = !formulario;
    });

    modal.addEventListener('hidden.bs.modal', function () {
        formulario = null;
        confirmar.disabled = true;
    });

    confirmar.addEventListener('click', function () {
        if (formulario && formulario.isConnected) {
            confirmar.disabled = true;
            formulario.submit();
        }
    });

    if (!window.bootstrap || !window.bootstrap.Modal) {
        document.addEventListener('click', function (evento) {
            var boton = evento.target.closest('form.nota-eliminar button');
            if (!boton) return;
            var aviso = document.getElementById('nota-eliminar-error');
            if (!aviso) {
                aviso = document.createElement('p');
                aviso.id = 'nota-eliminar-error';
                aviso.className = 'alert alert-danger';
                aviso.setAttribute('role', 'alert');
                aviso.textContent = 'No se pudo abrir la confirmación. Recarga la página e inténtalo nuevamente.';
                boton.closest('form').parentElement.appendChild(aviso);
            }
        });
    }
}());
