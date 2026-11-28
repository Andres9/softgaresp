(function () {
    'use strict';
    const form = document.querySelector('.nota-formulario');
    const lista = form.querySelector('.nota-servicios-lista');
    const agregar = form.querySelector('.nota-servicio-agregar');
    const total = form.querySelector('[name="costoServicio"]');
    const anticipo = form.querySelector('[name="anticipo"]');
    const pagado = form.querySelector('[name="pagado"]');
    const restante = form.querySelector('[name="restante"]');

    function calcular() {
        let suma = 0;
        lista.querySelectorAll('.nota-servicio-precio').forEach(function (precio) {
            suma += Number(precio.value) || 0;
        });
        const saldo = suma - (Number(anticipo.value) || 0) - (Number(pagado.value) || 0);
        total.value = suma;
        restante.value = saldo;
        pagado.setCustomValidity(suma > 2147483647 ? 'El total supera el máximo permitido.' :
            saldo < 0 ? 'El anticipo y el pago adicional no pueden superar el total.' : '');
    }

    const plantilla = lista.firstElementChild.cloneNode(true);

    function vaciar(linea) {
        linea.querySelector('input').value = '0';
        const selector = linea.querySelector('select');
        if (window.jQuery) {
            window.jQuery(selector).val('').trigger('change');
        } else {
            selector.value = '';
        }
    }

    agregar.addEventListener('click', function () {
        if (lista.children.length >= 200) {
            alert('La nota admite hasta 200 servicios.');
            return;
        }
        const linea = plantilla.cloneNode(true);
        linea.querySelector('select').value = '';
        linea.querySelector('input').value = '0';
        lista.appendChild(linea);
        if (window.inicializarSelectores) window.inicializarSelectores(linea.querySelector('select'));
        linea.querySelector('select').focus();
        calcular();
    });
    lista.addEventListener('click', function (evento) {
        if (!evento.target.closest('.nota-servicio-quitar')) return;
        if (lista.children.length > 1) {
            evento.target.closest('.nota-servicio-linea').remove();
        } else {
            vaciar(lista.firstElementChild);
        }
        calcular();
    });
    function alCambiar(evento) {
        if (evento.target.matches('.nota-servicio-selector')) {
            const opcion = evento.target.selectedOptions[0];
            evento.target.closest('.nota-servicio-linea').querySelector('.nota-servicio-precio').value = opcion && opcion.dataset.precio || '0';
            calcular();
        }
    }
    lista.addEventListener('change', alCambiar);
    if (window.jQuery) {
        window.jQuery(lista).on('change', alCambiar);
    }
    form.addEventListener('input', calcular);
    calcular();
}());
