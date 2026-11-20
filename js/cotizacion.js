(function () {
  'use strict';

  var items = document.getElementById('cotizacion-items');
  var addButton = document.getElementById('cotizacion-add-item');

  if (!items || !addButton) {
    return;
  }

  var nextIndex = parseInt(items.getAttribute('data-next-index'), 10) || 1;

  function actualizarControles() {
    var cards = items.querySelectorAll('.cotizacion-item');
    cards.forEach(function (card, index) {
      var number = card.querySelector('.cotizacion-item-number');
      var remove = card.querySelector('.cotizacion-remove-item');
      number.textContent = String(index + 1).padStart(2, '0');
      remove.disabled = cards.length === 1;
    });
    addButton.disabled = cards.length >= 10;
  }

  addButton.addEventListener('click', function () {
    var cards = items.querySelectorAll('.cotizacion-item');
    if (cards.length >= 10) {
      return;
    }

    var index = nextIndex++;
    var card = cards[0].cloneNode(true);
    card.setAttribute('data-item-index', String(index));
    card.querySelectorAll('input, textarea').forEach(function (field) {
      field.name = field.name.replace(/items\[\d+\]/, 'items[' + index + ']')
        .replace(/imagenes\[\d+\]/, 'imagenes[' + index + ']');
      if (field.type === 'number') {
        field.value = field.name.indexOf('[cantidad]') !== -1 ? '1' :
          (field.name.indexOf('[impuesto_porcentaje]') !== -1 ? '16' : '0');
      } else {
        field.value = '';
      }
    });
    var imagenActual = card.querySelector('.cotizacion-image-current');
    if (imagenActual) {
      imagenActual.remove();
    }
    items.appendChild(card);
    actualizarControles();
    card.querySelector('input[name*="[descripcion]"]').focus();
  });

  items.addEventListener('click', function (event) {
    var button = event.target.closest('.cotizacion-remove-item');
    if (button && !button.disabled) {
      button.closest('.cotizacion-item').remove();
      actualizarControles();
    }
  });

  actualizarControles();
}());
