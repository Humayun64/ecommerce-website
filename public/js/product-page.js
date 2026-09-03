/* Product page: image gallery, variant picker, quantity stepper. */
(function () {
  'use strict';

  /* ---------- gallery ---------- */

  var mainImage = document.getElementById('galleryImage');

  document.querySelectorAll('.gthumb').forEach(function (thumb) {
    thumb.addEventListener('click', function () {
      if (!mainImage) return;
      mainImage.src = thumb.dataset.src;
      document.querySelectorAll('.gthumb').forEach(function (t) { t.classList.remove('on'); });
      thumb.classList.add('on');
    });
  });

  /* ---------- quantity ---------- */

  var qty = document.getElementById('qty');

  function clampQty(max) {
    if (!qty) return;
    var v = parseInt(qty.value, 10);
    if (isNaN(v) || v < 1) v = 1;
    if (max && v > max) v = max;
    qty.value = v;
  }

  var up = document.getElementById('qtyUp');
  var down = document.getElementById('qtyDown');

  if (up) up.addEventListener('click', function () { qty.value = parseInt(qty.value, 10) + 1; clampQty(currentMax()); });
  if (down) down.addEventListener('click', function () { qty.value = parseInt(qty.value, 10) - 1; clampQty(currentMax()); });
  if (qty) qty.addEventListener('change', function () { clampQty(currentMax()); });

  /* ---------- variants ---------- */

  var dataEl = document.getElementById('variantData');
  if (!dataEl) return;

  var variants = JSON.parse(dataEl.textContent || '[]');
  var strings  = JSON.parse((document.getElementById('pdpStrings') || {}).textContent || '{}');

  if (!variants.length) return;

  var chosen = {};
  var active = null;

  var priceMain = document.getElementById('priceMain');
  var priceWas  = document.getElementById('priceWas');
  var priceSave = document.getElementById('priceSave');
  var stockLine = document.getElementById('stockLine');
  var skuLabel  = document.getElementById('skuLabel');
  var addBtn    = document.getElementById('addToCart');

  var axisCount = document.querySelectorAll('.axis-pick').length;

  function taka(n) {
    return '\u09F3' + Number(n).toLocaleString('en-IN');
  }

  function currentMax() {
    return active ? active.stock : null;
  }

  /** Value ids that still lead to a real variant, given what is chosen so far. */
  function reachableValues(axisId) {
    var others = Object.keys(chosen)
      .filter(function (a) { return a !== axisId; })
      .map(function (a) { return chosen[a]; });

    var reachable = {};

    variants.forEach(function (variant) {
      var ids = variant.values.map(String);
      var matchesOthers = others.every(function (v) { return ids.indexOf(String(v)) !== -1; });
      if (matchesOthers) {
        ids.forEach(function (id) { reachable[id] = true; });
      }
    });

    return reachable;
  }

  function findVariant() {
    var picked = Object.keys(chosen).map(function (a) { return String(chosen[a]); });

    if (picked.length < axisCount) return null;

    return variants.find(function (variant) {
      var ids = variant.values.map(String);
      return picked.every(function (p) { return ids.indexOf(p) !== -1; }) &&
             ids.length === picked.length;
    }) || null;
  }

  function render() {
    // Grey out combinations that do not exist rather than letting
    // someone pick a pair we never stocked.
    document.querySelectorAll('.axis-pick').forEach(function (block) {
      var axisId = block.dataset.axis;
      var reachable = reachableValues(axisId);

      block.querySelectorAll('.opt').forEach(function (btn) {
        var exists = reachable[btn.dataset.value];
        btn.classList.toggle('dead', !exists);
        btn.classList.toggle('on', String(chosen[axisId]) === btn.dataset.value);
      });
    });

    active = findVariant();

    if (!active) {
      var field = document.getElementById('variantId');
      if (field) field.value = '';
      if (stockLine) stockLine.innerHTML = '<span class="muted">' + (strings.choose || '') + '</span>';
      if (addBtn) addBtn.disabled = true;
      return;
    }

    if (priceMain) priceMain.textContent = taka(active.price);

    if (priceWas) {
      if (active.compare && active.compare > active.price) {
        priceWas.textContent = taka(active.compare);
        priceWas.style.display = '';
        if (priceSave) {
          priceSave.textContent = (strings.save || 'Save') + ' ' +
            Math.round(100 - (active.price / active.compare * 100)) + '%';
          priceSave.style.display = '';
        }
      } else {
        priceWas.style.display = 'none';
        if (priceSave) priceSave.style.display = 'none';
      }
    }

    if (skuLabel) skuLabel.textContent = active.sku;

    if (stockLine) {
      if (active.stock <= 0) {
        stockLine.innerHTML = '<span class="out">' + (strings.outStock || '') + '</span>';
      } else if (active.stock <= 5) {
        stockLine.innerHTML = '<span class="low">' +
          (strings.lowStock || '').replace(':n', active.stock) + '</span>';
      } else {
        stockLine.innerHTML = '<span class="ok">' + (strings.inStock || '') + '</span>';
      }
    }

    var variantField = document.getElementById('variantId');
    if (variantField) variantField.value = active.id;

    if (addBtn) {
      addBtn.disabled = active.stock <= 0;
      addBtn.textContent = active.stock <= 0
        ? (strings.soldOut || 'Sold out')
        : (strings.addToCart || 'Add to cart');
    }

    clampQty(active.stock);
  }

  document.querySelectorAll('.opt').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var axisId = btn.dataset.axis;

      if (String(chosen[axisId]) === btn.dataset.value) {
        delete chosen[axisId];
      } else {
        chosen[axisId] = btn.dataset.value;
      }

      render();
    });
  });

  // Preselect when there is only one variant, or one in-stock option per axis.
  if (variants.length === 1) {
    document.querySelectorAll('.opt').forEach(function (btn) {
      chosen[btn.dataset.axis] = btn.dataset.value;
    });
  }

  render();
})();
