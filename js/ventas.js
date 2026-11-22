(function () {
    'use strict';

    var contenedor = document.getElementById('productos-venta');
    var plantilla = document.getElementById('plantilla-producto-venta');
    var agregar = document.getElementById('agregar-producto-venta');
    var total = document.getElementById('total-venta');
    if (!contenedor || !plantilla || !agregar || !total) return;

    var siguienteIndice = contenedor.querySelectorAll('.venta-producto').length;
    var formato = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });

    function actualizar() {
        var suma = 0;
        var productos = contenedor.querySelectorAll('.venta-producto');
        productos.forEach(function (producto) {
            var cantidad = Number(producto.querySelector('.venta-cantidad').value) || 0;
            var precio = Number(producto.querySelector('.venta-precio').value) || 0;
            var subtotal = cantidad * Math.round(precio * 100);
            suma += subtotal;
            producto.querySelector('.venta-subtotal').textContent = formato.format(subtotal / 100);
            producto.querySelector('.venta-quitar').disabled = productos.length === 1;
        });
        total.textContent = formato.format(suma / 100);
        agregar.disabled = productos.length >= 100;
    }

    agregar.addEventListener('click', function () {
        var fragmento = plantilla.content.cloneNode(true);
        fragmento.querySelectorAll('[name]').forEach(function (campo) {
            campo.name = campo.name.replace('__INDEX__', siguienteIndice);
        });
        siguienteIndice++;
        var producto = fragmento.querySelector('.venta-producto');
        contenedor.appendChild(fragmento);
        if (window.inicializarSelectores) {
            window.inicializarSelectores(producto.querySelectorAll('select.select'));
        }
        actualizar();
        producto.querySelector('.venta-cantidad').focus();
    });

    contenedor.addEventListener('click', function (evento) {
        var boton = evento.target.closest('.venta-quitar');
        if (!boton || contenedor.querySelectorAll('.venta-producto').length <= 1) return;
        var producto = boton.closest('.venta-producto');
        if (window.jQuery && window.jQuery.fn.select2) {
            window.jQuery(producto).find('select.select2-hidden-accessible').select2('destroy');
        }
        producto.remove();
        actualizar();
    });
    contenedor.addEventListener('input', actualizar);
    actualizar();
}());
