document.addEventListener('DOMContentLoaded', function () {
  const tipo = document.getElementById('nuevo-movimiento-tipo');
  const categoria = document.getElementById('nuevo-movimiento-categoria');

  if (!tipo || !categoria) {
    return;
  }

  function actualizarCategorias() {
    Array.prototype.forEach.call(categoria.options, function (opcion) {
      const esGasto = opcion.value === 'Gastos';
      opcion.disabled = opcion.value !== '' && esGasto !== (tipo.value === 'gasto');
    });

    if (categoria.options[categoria.selectedIndex].disabled) {
      categoria.value = tipo.value === 'gasto' ? 'Gastos' : 'Otros ingresos';
    }
  }

  tipo.addEventListener('change', actualizarCategorias);
  actualizarCategorias();
});
