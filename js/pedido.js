(function () {
  'use strict';

  var form = document.getElementById('pedido-form');
  var tableBody = document.getElementById('pedido-items');
  var addButton = document.getElementById('pedido-add');
  var provider = document.getElementById('pedido-proveedor');
  var catalogMessage = document.getElementById('pedido-catalog-message');

  if (!form || !tableBody || !addButton || !provider || !catalogMessage) {
    return;
  }

  var editingId = form.getAttribute('data-editar');
  var draftBaseKey = 'softgaresp.pedido.draft.' + form.getAttribute('data-user');
  var storageKey = draftBaseKey + (editingId && editingId !== '0' ? '.edit.' + editingId : '');
  var products = [];
  var catalogRequest = 0;

  function number(value) {
    var parsed = parseFloat(String(value).replace(',', '.'));
    return isNaN(parsed) || !isFinite(parsed) ? 0 : parsed;
  }

  function isOneHourText(model, description) {
    var text = (String(model || '') + ' ' + String(description || ''))
      .normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    return /\b(?:1|una)\s*(?:hora|hr)\b/.test(text);
  }

  function isOneHourProduct(product) {
    return isOneHourText(product.codigo, (product.marca || '') + ' ' + (product.descripcion || ''));
  }

  function money(value) {
    return '$' + number(value).toLocaleString('es-MX', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  function roundMoney(value) {
    return Math.round((number(value) + Number.EPSILON) * 100) / 100;
  }

  function showCatalogMessage(message, isError) {
    catalogMessage.textContent = message;
    catalogMessage.classList.toggle('is-error', !!isError);
  }

  function snapshot() {
    var rows = Array.prototype.slice.call(tableBody.querySelectorAll('.pedido-row'));
    return {
      fecha: form.elements.fecha_pedido.value,
      proveedor: provider.value,
      envio: form.elements.envio.value,
      notas: form.elements.notas.value,
      items: rows.map(function (row) {
        var values = {};
        row.querySelectorAll('input').forEach(function (input) {
          var match = input.name.match(/\[([^\]]+)\]$/);
          if (match) {
            values[match[1]] = input.value;
          }
        });
        values.precioCompraManual = row.querySelector('[name$="[precio_compra]"]').dataset.manual === 'true';
        values.precioFinalManual = row.querySelector('[name$="[precio_final]"]').dataset.manual === 'true';
        return values;
      })
    };
  }

  function saveDraft() {
    try {
      sessionStorage.setItem(storageKey, JSON.stringify(snapshot()));
    } catch (error) {
      showCatalogMessage('No se pudo conservar el borrador en este navegador.', true);
    }
  }

  function fillRow(row, values) {
    Object.keys(values).forEach(function (key) {
      var field = row.querySelector('[name$="[' + key + ']"]');
      if (field && key !== 'precioFinalManual' && key !== 'precioCompraManual') {
        field.value = values[key];
      }
    });
    row.querySelector('[name$="[precio_compra]"]').dataset.manual = values.precioCompraManual ? 'true' : '';
    var finalInput = row.querySelector('[name$="[precio_final]"]');
    finalInput.dataset.manual = values.precioFinalManual ? 'true' : '';
    var image = row.querySelector('.pedido-product-image');
    var imageName = values.imagen || '';
    if (/^[A-Za-z0-9][A-Za-z0-9._-]{0,249}\.(jpg|jpeg|png)$/i.test(imageName)) {
      image.src = 'uploads/catalogo_pixal/' + encodeURIComponent(imageName);
      image.alt = values.descripcion || '';
      image.hidden = false;
      image.onload = function () { image.parentElement.classList.add('has-image'); };
      image.onerror = function () {
        image.hidden = true;
        image.parentElement.classList.remove('has-image');
        showCatalogMessage('No se pudo cargar la imagen del producto guardado.', true);
      };
    } else {
      image.removeAttribute('src');
      image.hidden = true;
      image.parentElement.classList.remove('has-image');
    }
    if (editingId && editingId !== '0') {
      row.dataset.appliedCode = String(values.modelo || '').trim().toLowerCase();
    }
  }

  function renderItems(items) {
    var firstRow = tableBody.querySelector('.pedido-row');
    tableBody.innerHTML = '';
    items.forEach(function (item, index) {
      var row = firstRow.cloneNode(true);
      row.querySelectorAll('input').forEach(function (input) {
        input.name = input.name.replace(/items\[\d+\]/, 'items[' + index + ']');
        if (input.classList.contains('pedido-output')) {
          input.value = '$0.00';
        }
      });
      fillRow(row, item);
      tableBody.appendChild(row);
    });
    updateRemoveButtons();
    tableBody.querySelectorAll('.pedido-row').forEach(calculateRow);
  }

  function loadDraft() {
    if (window.pedidoUpdated) {
      try {
        sessionStorage.removeItem(draftBaseKey + '.edit.' + window.pedidoUpdated);
      } catch (error) {
        showCatalogMessage('No se pudo limpiar el borrador de edición.', true);
      }
    }
    if (window.pedidoClearDraft) {
      try {
        sessionStorage.removeItem(storageKey);
      } catch (error) {
        showCatalogMessage('No se pudo limpiar el borrador guardado.', true);
      }
      return false;
    }

    var saved;
    try {
      saved = sessionStorage.getItem(storageKey);
    } catch (error) {
      showCatalogMessage('No se pudo leer el borrador guardado.', true);
      return false;
    }
    if (!saved) {
      return false;
    }

    try {
      var draft = JSON.parse(saved);
      if (!draft || !Array.isArray(draft.items) || draft.items.length === 0) {
        throw new Error('Borrador no válido.');
      }
      form.elements.fecha_pedido.value = draft.fecha || form.elements.fecha_pedido.value;
      provider.value = draft.proveedor || '';
      form.elements.envio.value = draft.envio || '0';
      form.elements.notas.value = draft.notas || '';
      renderItems(draft.items);
      return true;
    } catch (error) {
      showCatalogMessage('El borrador guardado está dañado y no se pudo recuperar.', true);
      try {
        sessionStorage.removeItem(storageKey);
      } catch (storageError) {
        showCatalogMessage('No se pudo retirar el borrador dañado.', true);
      }
      return false;
    }
  }

  function calculateRow(row, eventTarget) {
    var quantity = number(row.querySelector('[name$="[cantidad]"]').value);
    var purchase = number(row.querySelector('[name$="[precio_compra]"]').value);
    var finalInput = row.querySelector('[name$="[precio_final]"]');
    var code = row.querySelector('[name$="[modelo]"]').value;
    var product = provider.value === 'Pixal Center' ? productForCode(code) : null;
    var purchaseTax = !!product && isOneHourProduct(product);
    row.querySelector('.pedido-purchase-tax-hint').hidden = !purchaseTax;
    if (eventTarget === finalInput) {
      finalInput.dataset.manual = 'true';
    }
    if (finalInput.dataset.manual !== 'true') {
      finalInput.value = roundMoney(purchase).toFixed(2);
    }

    var finalPrice = number(finalInput.value);
    var margin = finalPrice > 0 ? (finalPrice - purchase) / finalPrice * 100 : null;
    var roundedMargin = margin === null ? null :
      (margin < 0 ? -roundMoney(-margin) : roundMoney(margin));
    row.querySelector('[name$="[margen_porcentaje]"]').value =
      roundedMargin === null ? 'No definido' : roundedMargin.toFixed(2) + '%';
    finalInput.setCustomValidity(roundedMargin !== null && roundedMargin < -999.99 ?
      'El margen de pérdida no puede ser inferior a -999.99%.' : '');
    var profit = finalPrice - purchase;
    row.querySelector('[name$="[total_compra]"]').value = money(quantity * purchase);
    row.querySelector('[name$="[ganancia_individual]"]').value = money(profit);
    row.querySelector('[name$="[ganancia_total]"]').value = money(profit * quantity);
    row.querySelector('[name$="[total_venta]"]').value = money(finalPrice * quantity);
    updateTotals();
  }

  function updateTotals() {
    var purchaseTotal = 0;
    var profitTotal = 0;
    var saleTotal = 0;
    var shipping = number(form.elements.envio.value);

    tableBody.querySelectorAll('.pedido-row').forEach(function (row) {
      var quantity = number(row.querySelector('[name$="[cantidad]"]').value);
      var purchase = number(row.querySelector('[name$="[precio_compra]"]').value);
      var finalPrice = number(row.querySelector('[name$="[precio_final]"]').value);
      purchaseTotal += quantity * purchase;
      profitTotal += (finalPrice - purchase) * quantity;
      saleTotal += finalPrice * quantity;
    });

    purchaseTotal += shipping;
    profitTotal -= shipping;
    document.getElementById('pedido-total-compra').textContent = money(purchaseTotal);
    document.getElementById('pedido-total-ganancia').textContent = money(profitTotal);
    document.getElementById('pedido-total-venta').textContent = money(saleTotal);
  }

  function updateRemoveButtons() {
    var rows = tableBody.querySelectorAll('.pedido-row');
    rows.forEach(function (row, index) {
      row.querySelectorAll('input').forEach(function (input) {
        input.name = input.name.replace(/items\[\d+\]/, 'items[' + index + ']');
      });
      var modelInput = row.querySelector('[name$="[modelo]"]');
      var suggestions = row.querySelector('.pedido-suggestions');
      modelInput.id = 'pedido-modelo-' + index;
      modelInput.setAttribute('aria-controls', 'pedido-sugerencias-' + index);
      row.querySelector('.pedido-model > label').htmlFor = modelInput.id;
      suggestions.id = 'pedido-sugerencias-' + index;
      closeSuggestions(row);
      row.querySelector('.pedido-remove').disabled = rows.length === 1;
    });
  }

  function closeSuggestions(row) {
    var input = row.querySelector('[name$="[modelo]"]');
    var list = row.querySelector('.pedido-suggestions');
    list.hidden = true;
    list.textContent = '';
    input.setAttribute('aria-expanded', 'false');
    input.removeAttribute('aria-activedescendant');
  }

  function showSuggestions(row) {
    var input = row.querySelector('[name$="[modelo]"]');
    var list = row.querySelector('.pedido-suggestions');
    var query = input.value.trim().toLowerCase();
    closeSuggestions(row);
    if (provider.value !== 'Pixal Center' || !query) {
      return;
    }

    var matches = products.filter(function (product) {
      return product.codigo.toLowerCase().indexOf(query) !== -1;
    });
    matches.sort(function (a, b) {
      return (a.codigo.toLowerCase().indexOf(query) === 0 ? 0 : 1) -
        (b.codigo.toLowerCase().indexOf(query) === 0 ? 0 : 1);
    });
    matches.slice(0, 5).forEach(function (product, index) {
      var option = document.createElement('div');
      option.className = 'pedido-suggestion';
      option.id = list.id + '-opcion-' + index;
      option.setAttribute('role', 'option');
      option.setAttribute('aria-selected', 'false');
      option.dataset.code = product.codigo;

      var thumbnail = document.createElement('span');
      thumbnail.className = 'pedido-suggestion-image';
      if (product.imagen) {
        var image = document.createElement('img');
        image.src = product.imagen;
        image.alt = '';
        thumbnail.appendChild(image);
      } else {
        var icon = document.createElement('i');
        icon.className = 'fa-regular fa-image';
        icon.setAttribute('aria-hidden', 'true');
        thumbnail.appendChild(icon);
      }
      var info = document.createElement('span');
      info.className = 'pedido-suggestion-info';
      var code = document.createElement('strong');
      code.textContent = product.codigo;
      var description = document.createElement('span');
      description.textContent = product.descripcion;
      info.appendChild(code);
      info.appendChild(description);
      option.appendChild(thumbnail);
      option.appendChild(info);
      list.appendChild(option);
    });
    if (list.children.length) {
      list.hidden = false;
      input.setAttribute('aria-expanded', 'true');
    }
  }

  function selectSuggestion(row, option) {
    var input = row.querySelector('[name$="[modelo]"]');
    input.value = option.dataset.code;
    input.focus();
    closeSuggestions(row);
    applyProduct(row);
    saveDraft();
  }

  function productForCode(code) {
    var normalized = String(code).trim().toLowerCase();
    return products.find(function (product) {
      return product.codigo.toLowerCase() === normalized;
    });
  }

  function applyProduct(row, preservePrice) {
    var code = row.querySelector('[name$="[modelo]"]').value;
    var product = productForCode(code);
    if (!product) {
      row.dataset.appliedCode = '';
      if (provider.value === 'Pixal Center') {
        row.querySelector('[name$="[descripcion]"]').value = '';
        row.querySelector('[name$="[imagen]"]').value = '';
        var clearedPurchase = row.querySelector('[name$="[precio_compra]"]');
        clearedPurchase.value = '0';
        clearedPurchase.dataset.manual = '';
        row.querySelector('[name$="[precio_final]"]').dataset.manual = '';
        var staleImage = row.querySelector('.pedido-product-image');
        staleImage.removeAttribute('src');
        staleImage.hidden = true;
        staleImage.parentElement.classList.remove('has-image');
      }
      calculateRow(row);
      return;
    }
    var normalizedCode = String(product.codigo).trim().toLowerCase();
    if (row.dataset.appliedCode === normalizedCode) {
      return;
    }
    row.dataset.appliedCode = normalizedCode;
    row.querySelector('[name$="[descripcion]"]').value =
      product.descripcion;
    var purchaseInput = row.querySelector('[name$="[precio_compra]"]');
    if (!preservePrice || purchaseInput.dataset.manual !== 'true') {
      purchaseInput.value =
        roundMoney(number(product.precio) * (isOneHourProduct(product) ? 1.08 : 1)).toFixed(2);
      purchaseInput.dataset.manual = '';
    }
    row.querySelector('[name$="[imagen]"]').value = product.imagen ? product.imagen.split('/').pop() : '';
    var image = row.querySelector('.pedido-product-image');
    var imagePreview = image.parentElement;
    imagePreview.classList.remove('has-image');
    if (product.imagen) {
      var imageUrl = product.imagen;
      image.alt = product.descripcion;
      image.onload = function () {
        if (image.getAttribute('src') === imageUrl) {
          imagePreview.classList.add('has-image');
        }
      };
      image.onerror = function () {
        if (image.getAttribute('src') === imageUrl) {
          image.hidden = true;
          imagePreview.classList.remove('has-image');
          showCatalogMessage('No se pudo cargar la imagen del modelo ' + product.codigo + '.', true);
        }
      };
      image.src = imageUrl;
      image.hidden = false;
    } else {
      image.removeAttribute('src');
      image.hidden = true;
      imagePreview.classList.remove('has-image');
      showCatalogMessage('Este modelo no tiene una imagen cargada en el catálogo.', true);
    }
    var finalInput = row.querySelector('[name$="[precio_final]"]');
    if (!preservePrice) {
      finalInput.dataset.manual = '';
    }
    calculateRow(row);
    saveDraft();
  }

  function loadCatalog(preserveRows) {
    var request = ++catalogRequest;
    products = [];
    tableBody.querySelectorAll('.pedido-row').forEach(closeSuggestions);
    if (provider.value !== 'Pixal Center') {
      showCatalogMessage('El catálogo de este proveedor aún no está cargado. Puedes ingresar los productos manualmente.', false);
      return Promise.resolve();
    }
    showCatalogMessage('Cargando catálogo de Pixal Center…', false);
    return fetch('productosPedido.php?proveedor=' + encodeURIComponent(provider.value), {
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json' }
    }).then(function (response) {
      if (!response.ok) {
        throw new Error('Respuesta HTTP ' + response.status);
      }
      return response.json();
    }).then(function (data) {
      if (request !== catalogRequest) {
        return;
      }
      if (!Array.isArray(data)) {
        throw new Error(data && data.error ? data.error : 'El catálogo tiene un formato no válido.');
      }
      products = data;
      showCatalogMessage(data.length ?
        'Catálogo Pixal Center: ' + data.length + ' modelos disponibles.' :
        'El catálogo Pixal Center aún no tiene productos cargados.', data.length === 0);
      tableBody.querySelectorAll('.pedido-row').forEach(function (row) {
        if (preserveRows) {
          calculateRow(row);
        } else {
          applyProduct(row, true);
        }
      });
      var active = document.activeElement;
      if (active && active.name && active.name.indexOf('[modelo]') !== -1) {
        showSuggestions(active.closest('.pedido-row'));
      }
    }).catch(function (error) {
      if (request !== catalogRequest) {
        return;
      }
      showCatalogMessage('No se pudo cargar el catálogo de Pixal Center. ' + error.message, true);
    });
  }

  addButton.addEventListener('click', function () {
    var row = tableBody.querySelector('.pedido-row').cloneNode(true);
    row.dataset.appliedCode = '';
    row.querySelectorAll('input').forEach(function (input) {
      if (input.classList.contains('pedido-output')) {
        input.value = '$0.00';
      } else if (input.type === 'number') {
        input.value = input.name.indexOf('[cantidad]') !== -1 ? '1' : '0';
      } else {
        input.value = '';
      }
    });
    var image = row.querySelector('.pedido-product-image');
    image.removeAttribute('src');
    image.hidden = true;
    image.parentElement.classList.remove('has-image');
    var finalInput = row.querySelector('[name$="[precio_final]"]');
    finalInput.dataset.manual = '';
    delete finalInput.dataset.basePrice;
    row.querySelector('[name$="[precio_compra]"]').dataset.manual = '';
    tableBody.appendChild(row);
    updateRemoveButtons();
    calculateRow(row);
    saveDraft();
    row.querySelector('[name$="[modelo]"]').focus();
  });

  tableBody.addEventListener('click', function (event) {
    var option = event.target.closest('.pedido-suggestion');
    if (option) {
      selectSuggestion(option.closest('.pedido-row'), option);
      return;
    }
    var button = event.target.closest('.pedido-remove');
    if (button && !button.disabled) {
      button.closest('.pedido-row').remove();
      updateRemoveButtons();
      updateTotals();
      saveDraft();
    }
  });

  tableBody.addEventListener('input', function (event) {
    if (event.target.name.indexOf('[precio_compra]') !== -1) {
      event.target.dataset.manual = 'true';
    }
    if (event.target.name.indexOf('[modelo]') !== -1) {
      var modelRow = event.target.closest('.pedido-row');
      applyProduct(modelRow);
      showSuggestions(modelRow);
    } else if (event.target.name.indexOf('[descripcion]') !== -1) {
      calculateRow(event.target.closest('.pedido-row'));
    }
    if (event.target.name.indexOf('[cantidad]') !== -1 ||
        event.target.name.indexOf('[precio_compra]') !== -1 ||
        event.target.name.indexOf('[precio_final]') !== -1) {
      calculateRow(event.target.closest('.pedido-row'), event.target);
    }
    saveDraft();
  });

  tableBody.addEventListener('change', function (event) {
    if (event.target.name.indexOf('[modelo]') !== -1) {
      applyProduct(event.target.closest('.pedido-row'));
      saveDraft();
    }
  });

  tableBody.addEventListener('focusin', function (event) {
    if (event.target.name && event.target.name.indexOf('[modelo]') !== -1) {
      showSuggestions(event.target.closest('.pedido-row'));
    }
  });

  tableBody.addEventListener('focusout', function (event) {
    if (event.target.name && event.target.name.indexOf('[modelo]') !== -1) {
      var row = event.target.closest('.pedido-row');
      setTimeout(function () {
        if (document.activeElement !== event.target) {
          closeSuggestions(row);
        }
      }, 150);
    }
  });

  tableBody.addEventListener('keydown', function (event) {
    if (!event.target.name || event.target.name.indexOf('[modelo]') === -1) {
      return;
    }
    var row = event.target.closest('.pedido-row');
    var list = row.querySelector('.pedido-suggestions');
    if (event.key === 'Escape') {
      closeSuggestions(row);
      return;
    }
    if (event.key === 'ArrowDown' && list.hidden) {
      showSuggestions(row);
    }
    var options = Array.prototype.slice.call(list.querySelectorAll('[role="option"]'));
    if (list.hidden || !options.length) {
      return;
    }
    var selected = options.findIndex(function (option) {
      return option.getAttribute('aria-selected') === 'true';
    });
    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
      event.preventDefault();
      var next = event.key === 'ArrowDown' ?
        Math.min(selected + 1, options.length - 1) :
        (selected < 0 ? options.length - 1 : Math.max(selected - 1, 0));
      options.forEach(function (option, index) {
        option.setAttribute('aria-selected', index === next ? 'true' : 'false');
      });
      event.target.setAttribute('aria-activedescendant', options[next].id);
    } else if (event.key === 'Enter' && selected >= 0) {
      event.preventDefault();
      selectSuggestion(row, options[selected]);
    }
  });

  form.addEventListener('input', saveDraft);
  form.addEventListener('change', saveDraft);
  form.elements.envio.addEventListener('input', updateTotals);
  provider.addEventListener('change', function () {
    tableBody.querySelectorAll('.pedido-row').forEach(calculateRow);
    loadCatalog().then(function () {
      tableBody.querySelectorAll('.pedido-row').forEach(calculateRow);
      updateTotals();
      saveDraft();
    });
  });

  var restoredDraft = loadDraft();
  if (editingId && editingId !== '0') {
    if (!restoredDraft) {
      if (!Array.isArray(window.pedidoEditItems) || !window.pedidoEditItems.length) {
        showCatalogMessage('No se pudieron cargar los productos del pedido para editarlo.', true);
        return;
      }
      renderItems(window.pedidoEditItems.map(function (item) {
        item.precioCompraManual = true;
        item.precioFinalManual = true;
        return item;
      }));
    }
    form.querySelector('button[type="submit"]').disabled = false;
  }
  loadCatalog(!!editingId && editingId !== '0');
}());
