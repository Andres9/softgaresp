(function () {
    'use strict';

    const equipo = document.querySelector('.nota-equipos-lista');
    const form = document.querySelector('.nota-formulario');
    const seccion = document.getElementById('nota-patron-seccion');
    const valor = document.getElementById('nota-patron-valor');
    const tablero = document.querySelector('.nota-patron');
    const lineas = tablero.querySelector('polyline');
    const puntos = Array.from(tablero.querySelectorAll('.nota-patron-punto'));
    const estado = document.querySelector('.nota-patron-estado');
    const limpiar = document.querySelector('.nota-patron-limpiar');
    const clave = document.getElementById('nota-clave');
    const mostrarClave = document.querySelector('.nota-clave-toggle');
    let secuencia = valor.value ? valor.value.split('').map(Number) : [];
    let dibujando = false;

    function actualizar() {
        valor.value = seccion.hidden ? '' : secuencia.join('');
        puntos.forEach(function (boton) {
            boton.setAttribute('aria-pressed', secuencia.includes(Number(boton.dataset.punto)) ? 'true' : 'false');
        });
        lineas.setAttribute('points', secuencia.map(function (numero) {
            const indice = numero - 1;
            return (40 + (indice % 3) * 80) + ',' + (40 + Math.floor(indice / 3) * 80);
        }).join(' '));
        estado.textContent = secuencia.length
            ? secuencia.length + ' puntos seleccionados' + (secuencia.length < 4 ? '; se requieren al menos cuatro.' : '.')
            : 'No se ha registrado un patrón.';
    }

    function agregar(numero) {
        if (secuencia.includes(numero)) return;
        if (secuencia.length) {
            const anterior = secuencia[secuencia.length - 1] - 1;
            const actual = numero - 1;
            const x1 = anterior % 3;
            const y1 = Math.floor(anterior / 3);
            const x2 = actual % 3;
            const y2 = Math.floor(actual / 3);
            if ((x1 + x2) % 2 === 0 && (y1 + y2) % 2 === 0 && Math.max(Math.abs(x2 - x1), Math.abs(y2 - y1)) === 2) {
                const intermedio = ((y1 + y2) / 2) * 3 + (x1 + x2) / 2 + 1;
                if (!secuencia.includes(intermedio)) secuencia.push(intermedio);
            }
        }
        secuencia.push(numero);
        actualizar();
    }

    function actualizarEquipo() {
        seccion.hidden = !Array.from(equipo.querySelectorAll('.nota-equipo-selector')).some(function (selector) {
            const opcion = selector.options[selector.selectedIndex];
            return opcion && opcion.dataset.patron === '1';
        });
        actualizar();
    }

    equipo.addEventListener('change', actualizarEquipo);
    if (window.jQuery) {
        window.jQuery(equipo).on('change', actualizarEquipo);
    }
    mostrarClave.addEventListener('click', function () {
        const visible = clave.type === 'password';
        clave.type = visible ? 'text' : 'password';
        mostrarClave.textContent = visible ? 'Ocultar' : 'Mostrar';
        mostrarClave.setAttribute('aria-pressed', visible ? 'true' : 'false');
    });
    limpiar.addEventListener('click', function () {
        secuencia = [];
        actualizar();
    });
    puntos.forEach(function (boton) {
        boton.addEventListener('click', function (evento) {
            if (evento.detail === 0 && tablero.dataset.deshabilitado !== '1') {
                agregar(Number(boton.dataset.punto));
            }
        });
    });
    tablero.addEventListener('pointerdown', function (evento) {
        if (tablero.dataset.deshabilitado === '1' || evento.button !== 0) return;
        const boton = evento.target.closest('.nota-patron-punto');
        if (!boton) return;
        dibujando = true;
        tablero.setPointerCapture(evento.pointerId);
        agregar(Number(boton.dataset.punto));
        evento.preventDefault();
    });
    tablero.addEventListener('pointermove', function (evento) {
        if (!dibujando) return;
        const elemento = document.elementFromPoint(evento.clientX, evento.clientY);
        const boton = elemento && elemento.closest('.nota-patron-punto');
        if (boton && tablero.contains(boton)) agregar(Number(boton.dataset.punto));
    });
    tablero.addEventListener('pointerup', function () { dibujando = false; });
    tablero.addEventListener('pointercancel', function () { dibujando = false; });
    form.addEventListener('submit', function (evento) {
        if (!seccion.hidden && !valor.disabled && secuencia.length > 0 && secuencia.length < 4) {
            evento.preventDefault();
            estado.textContent = 'El patrón debe contener al menos cuatro puntos.';
            puntos[0].focus();
        }
    });

    actualizar();
    actualizarEquipo();
}());
