document.addEventListener('DOMContentLoaded', function () {
  const formatearMoneda = function (valor) {
    const importe = Number.isFinite(valor) ? valor : 0;
    return '$' + importe.toLocaleString('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  };

  const obtenerImporte = function (input) {
    const importe = Number.parseFloat(input.value);
    return Number.isFinite(importe) ? importe : 0;
  };

  const sumarSeccion = function (seccion) {
    const contenedor = document.querySelector('[data-renglones="' + seccion + '"]');
    if (!contenedor) {
      return 0;
    }

    return Array.prototype.reduce.call(
      contenedor.querySelectorAll('input[name$="[importe][]"]'),
      function (total, input) {
        return total + obtenerImporte(input);
      },
      0
    );
  };

  const sumarCuentas = function () {
    return Array.prototype.reduce.call(
      document.querySelectorAll('[data-cuenta-saldo]'),
      function (total, input) { return total + Math.round(obtenerImporte(input) * 100); },
      0
    ) / 100;
  };

  const actualizarTotales = function () {
    const totalCuentas = sumarCuentas();
    const saldoCuentas = document.querySelector('[data-saldo="cuentas"]');
    const fondoCuentas = document.querySelector('[data-fondo-cuentas] input[type="number"]');
    if (saldoCuentas) {
      saldoCuentas.textContent = formatearMoneda(totalCuentas);
    }
    if (fondoCuentas) {
      fondoCuentas.value = totalCuentas.toFixed(2);
    }

    Array.prototype.forEach.call(document.querySelectorAll('[data-total]'), function (total) {
      total.textContent = formatearMoneda(sumarSeccion(total.dataset.total));
    });

    const inventarioActual = document.querySelector('[data-calculo="inventario-actual"]');
    const inventarioDeuda = document.querySelector('[data-calculo="inventario-deuda"]');
    const saldoInventario = document.querySelector('[data-saldo="inventario"]');
    const totalInventario = document.querySelector('[data-total="inventario"]');
    if (inventarioActual && inventarioDeuda && saldoInventario && totalInventario) {
      const saldo = obtenerImporte(inventarioActual) - obtenerImporte(inventarioDeuda);
      saldoInventario.textContent = formatearMoneda(saldo);
      totalInventario.textContent = formatearMoneda(saldo);
    }
  };

  const actualizarRequisitosCuenta = function (cuenta) {
    const saldo = cuenta.querySelector('[data-cuenta-saldo]');
    const fecha = cuenta.querySelector('[data-cuenta-fecha]');
    if (saldo && fecha) {
      saldo.required = fecha.value !== '';
      fecha.required = saldo.value !== '';
    }
  };

  const crearRenglon = function (seccion) {
    const renglon = document.createElement('div');
    renglon.className = 'cierre-renglon';

    const etiquetaConcepto = document.createElement('label');
    const textoConcepto = document.createElement('span');
    textoConcepto.textContent = 'Concepto';
    const concepto = document.createElement('input');
    concepto.className = 'form-control';
    concepto.type = 'text';
    concepto.name = 'items[' + seccion + '][concepto][]';
    concepto.maxLength = 80;
    concepto.setAttribute('aria-label', 'Nuevo concepto');
    etiquetaConcepto.appendChild(textoConcepto);
    etiquetaConcepto.appendChild(concepto);

    const etiquetaImporte = document.createElement('label');
    const textoImporte = document.createElement('span');
    textoImporte.textContent = 'Importe';
    const contenedorImporte = document.createElement('div');
    contenedorImporte.className = 'cierre-importe-input';
    const simbolo = document.createElement('span');
    simbolo.textContent = '$';
    const importe = document.createElement('input');
    importe.className = 'form-control';
    importe.type = 'number';
    importe.name = 'items[' + seccion + '][importe][]';
    importe.step = '0.01';
    importe.setAttribute('aria-label', 'Importe del nuevo concepto');
    contenedorImporte.appendChild(simbolo);
    contenedorImporte.appendChild(importe);
    etiquetaImporte.appendChild(textoImporte);
    etiquetaImporte.appendChild(contenedorImporte);

    const quitar = document.createElement('button');
    quitar.className = 'cierre-quitar-renglon';
    quitar.type = 'button';
    quitar.setAttribute('aria-label', 'Quitar concepto');
    const icono = document.createElement('i');
    icono.className = 'fa-solid fa-xmark';
    icono.setAttribute('aria-hidden', 'true');
    quitar.appendChild(icono);

    renglon.appendChild(etiquetaConcepto);
    renglon.appendChild(etiquetaImporte);
    renglon.appendChild(quitar);
    return renglon;
  };

  const formulario = document.querySelector('.cierre-formulario');
  const estadoBorrador = document.getElementById('cierre-borrador-estado');
  const descartarBorrador = document.getElementById('cierre-descartar-borrador');
  const revisionInput = formulario.querySelector('[name="borrador_revision"]');
  const claveBorrador = 'softgaresp-cierre-' + formulario.dataset.borradorClave;
  let revisionActual = null;
  let conflictoBorrador = false;
  let almacenamientoDisponible = true;
  let tieneSeccionesRetiradas = false;

  const mensajeSeccionesRetiradas = ' El borrador incluye datos de movimientos o gastos que ya no se guardarán desde este formulario.';

  const mostrarEstadoBorrador = function (mensaje, permitirDescartar) {
    estadoBorrador.hidden = false;
    estadoBorrador.querySelector('span').textContent = mensaje;
    descartarBorrador.hidden = !permitirDescartar;
  };

  const leerBorrador = function () {
    const texto = localStorage.getItem(claveBorrador);
    if (texto === null) {
      return null;
    }
    const borrador = JSON.parse(texto);
    if (!borrador || typeof borrador.revision !== 'string' ||
        !/^[a-z0-9-]{1,80}$/.test(borrador.revision) ||
        !borrador.campos || typeof borrador.campos !== 'object' ||
        !borrador.secciones || typeof borrador.secciones !== 'object') {
      throw new Error('El borrador no tiene un formato válido.');
    }
    return borrador;
  };

  const avisarErrorBorrador = function (error) {
    console.error('No se pudo utilizar el borrador del cierre:', error);
    almacenamientoDisponible = false;
    revisionInput.value = '';
    mostrarEstadoBorrador('No se pudo conservar el borrador en este navegador. Guarda el cierre antes de salir.', false);
  };

  const restaurarBorrador = function (borrador) {
    const secciones = formulario.querySelectorAll('[data-renglones]');
    let fondoAnterior = null;
    let contenedorFondo = null;
    Array.prototype.forEach.call(secciones, function (contenedor) {
      const filas = borrador.secciones[contenedor.dataset.renglones];
      const limite = contenedor.dataset.renglones === 'fondo' ? 31 : 30;
      if (!Array.isArray(filas) || filas.length > limite || filas.some(function (fila) {
        return !fila || typeof fila.concepto !== 'string' || typeof fila.importe !== 'string';
      })) {
        throw new Error('El borrador contiene conceptos inválidos.');
      }
    });
    Array.prototype.forEach.call(secciones, function (contenedor) {
      const seccion = contenedor.dataset.renglones;
      const fondoCuentas = contenedor.querySelector('[data-fondo-cuentas]');
      if (fondoCuentas) {
        contenedorFondo = contenedor;
      }
      contenedor.textContent = '';
      if (fondoCuentas) {
        contenedor.appendChild(fondoCuentas);
      }
      let fondoRestaurado = false;
      borrador.secciones[seccion].forEach(function (fila) {
        if (fondoCuentas && !fondoRestaurado && fila.concepto.trim().toLowerCase() === 'fondo') {
          fondoRestaurado = true;
          fondoAnterior = fila.importe;
          return;
        }
        const renglon = crearRenglon(seccion);
        renglon.querySelector('input[type="text"]').value = fila.concepto;
        renglon.querySelector('input[type="number"]').value = fila.importe;
        contenedor.appendChild(renglon);
      });
    });
    Array.prototype.forEach.call(formulario.querySelectorAll('input:not([type="hidden"])'), function (input) {
      if (!input.name.startsWith('items[') && typeof borrador.campos[input.name] === 'string') {
        input.value = borrador.campos[input.name];
      }
    });
    if (contenedorFondo && fondoAnterior !== null && fondoAnterior !== '' &&
        Number(fondoAnterior) !== sumarCuentas()) {
      const aviso = document.createElement('p');
      aviso.className = 'cierre-aviso-fondo';
      aviso.textContent = 'El fondo del borrador era ' + fondoAnterior +
        '. Se actualizará con el total de cuentas.';
      contenedorFondo.parentNode.insertBefore(aviso, contenedorFondo);
    }
  };

  const guardarBorrador = function () {
    if (!almacenamientoDisponible || conflictoBorrador) {
      return;
    }
    try {
      const existente = leerBorrador();
      if ((existente ? existente.revision : null) !== revisionActual) {
        conflictoBorrador = true;
        revisionInput.value = '';
        mostrarEstadoBorrador('El borrador cambió en otra pestaña. Copia tus cambios y recarga esta página antes de guardar.', false);
        return;
      }
      const borrador = {
        revision: Date.now().toString(36) + '-' + Math.random().toString(36).slice(2),
        campos: {},
        secciones: {}
      };
      if (existente) {
        ['movimientos', 'gastos'].forEach(function (seccion) {
          if (Object.prototype.hasOwnProperty.call(existente.secciones, seccion)) {
            borrador.secciones[seccion] = existente.secciones[seccion];
          }
        });
        if (Object.prototype.hasOwnProperty.call(existente.campos, 'gastos_base')) {
          borrador.campos.gastos_base = existente.campos.gastos_base;
        }
      }
      Array.prototype.forEach.call(formulario.querySelectorAll('input:not([type="hidden"])'), function (input) {
        if (input.name && !input.name.startsWith('items[')) {
          borrador.campos[input.name] = input.value;
        }
      });
      Array.prototype.forEach.call(formulario.querySelectorAll('[data-renglones]'), function (contenedor) {
        borrador.secciones[contenedor.dataset.renglones] = Array.prototype.map.call(
          contenedor.querySelectorAll('.cierre-renglon'),
          function (renglon) {
            return {
              concepto: renglon.querySelector('input[type="text"]').value,
              importe: renglon.querySelector('input[type="number"]').value
            };
          }
        );
      });
      localStorage.setItem(claveBorrador, JSON.stringify(borrador));
      revisionActual = borrador.revision;
      revisionInput.value = revisionActual;
      mostrarEstadoBorrador('Borrador conservado en este navegador para este mes. Se eliminará al guardar el cierre.' +
        (tieneSeccionesRetiradas ? mensajeSeccionesRetiradas : ''), true);
    } catch (error) {
      avisarErrorBorrador(error);
    }
  };

  try {
    const borrador = leerBorrador();
    if (borrador && formulario.dataset.borradorGuardado === borrador.revision) {
      localStorage.removeItem(claveBorrador);
    } else if (borrador) {
      restaurarBorrador(borrador);
      tieneSeccionesRetiradas = ['movimientos', 'gastos'].some(function (seccion) {
        return Object.prototype.hasOwnProperty.call(borrador.secciones, seccion);
      }) || Object.prototype.hasOwnProperty.call(borrador.campos, 'gastos_base');
      revisionActual = borrador.revision;
      revisionInput.value = revisionActual;
      mostrarEstadoBorrador('Se recuperó el borrador de este mes desde este navegador. Aún no se ha guardado el cierre.' +
        (tieneSeccionesRetiradas ? mensajeSeccionesRetiradas : ''), true);
    }
  } catch (error) {
    avisarErrorBorrador(error);
  }

  formulario.addEventListener('submit', function (evento) {
    if (tieneSeccionesRetiradas && !window.confirm(
      'El borrador contiene datos sin guardar de movimientos o gastos, que ya no se incluyen en el formulario. ' +
      'Si guardas este cierre, esos datos del borrador se descartarán. Los datos ya guardados se conservarán. ¿Continuar?'
    )) {
      evento.preventDefault();
      return;
    }
    guardarBorrador();
    if (conflictoBorrador) {
      evento.preventDefault();
    }
  });

  descartarBorrador.addEventListener('click', function () {
    if (!window.confirm('¿Descartar el borrador de este mes y recuperar los datos guardados?')) {
      return;
    }
    try {
      const existente = leerBorrador();
      if (!existente || existente.revision !== revisionActual) {
        conflictoBorrador = true;
        mostrarEstadoBorrador('El borrador cambió en otra pestaña. Copia tus cambios y recarga esta página.', false);
        return;
      }
      localStorage.removeItem(claveBorrador);
      window.location.reload();
    } catch (error) {
      avisarErrorBorrador(error);
    }
  });

  document.addEventListener('input', function (evento) {
    if (evento.target.matches('.cierre-renglon input, [data-calculo], [data-cuenta-saldo]')) {
      actualizarTotales();
    }
    if (evento.target.matches('[data-cuenta-saldo], [data-cuenta-fecha]')) {
      actualizarRequisitosCuenta(evento.target.closest('.cierre-cuenta'));
    }
    if (formulario.contains(evento.target)) {
      guardarBorrador();
    }
  });

  document.addEventListener('click', function (evento) {
    const agregar = evento.target.closest('[data-agregar]');
    if (agregar) {
      const seccion = agregar.dataset.agregar;
      const contenedor = document.querySelector('[data-renglones="' + seccion + '"]');
      const limite = seccion === 'fondo' ? 31 : 30;
      if (contenedor && contenedor.children.length < limite) {
        const renglon = crearRenglon(seccion);
        contenedor.appendChild(renglon);
        renglon.querySelector('input[type="text"]').focus();
        guardarBorrador();
      }
      return;
    }

    const quitar = evento.target.closest('.cierre-quitar-renglon');
    if (quitar) {
      const renglon = quitar.closest('.cierre-renglon');
      if (renglon) {
        renglon.remove();
        actualizarTotales();
        guardarBorrador();
      }
    }
  });

  document.querySelectorAll('.cierre-cuenta').forEach(actualizarRequisitosCuenta);
  actualizarTotales();
});
