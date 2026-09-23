/* Product page: gallery, variant picker, quantity, tabs, review form. */
(function () {
  'use strict';

  /* ================= gallery ================= */

  var mainImage = document.getElementById('galleryImage');

  document.querySelectorAll('.pp-thumb').forEach(function (thumb) {
    thumb.addEventListener('click', function () {
      if (!mainImage) return;
      mainImage.src = thumb.dataset.src;
      document.querySelectorAll('.pp-thumb').forEach(function (t) { t.classList.remove('on'); });
      thumb.classList.add('on');
    });
  });

  /* ================= tabs ================= */

  var tabs = document.querySelectorAll('.pp-tab');
  var panels = document.querySelectorAll('.pp-panel');

  function showTab(name) {
    tabs.forEach(function (t) {
      t.setAttribute('aria-selected', String(t.dataset.tab === name));
    });
    panels.forEach(function (p) {
      p.classList.toggle('on', p.dataset.panel === name);
    });
  }

  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () { showTab(tab.dataset.tab); });
  });

  // "126 reviews" under the title jumps to the reviews tab.
  document.querySelectorAll('[data-gotab]').forEach(function (link) {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      showTab(link.dataset.gotab);
      var anchor = document.getElementById('reviews');
      if (anchor) anchor.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  // Land on the reviews tab when the page was opened at #reviews, or when
  // a submitted review bounced back with errors or a thank-you.
  var wantsReviews = window.location.hash === '#reviews'
    || document.querySelector('.pp-errors')
    || document.querySelector('.pp-flash');

  if (wantsReviews) showTab('reviews');

  /* ================= review form ================= */

  var reviewForm = document.getElementById('reviewForm');
  var openReview = document.getElementById('writeReview');
  var cancelReview = document.getElementById('cancelReview');

  function toggleReviewForm(show) {
    if (!reviewForm) return;
    reviewForm.hidden = !show;
    if (show) {
      var first = reviewForm.querySelector('input[name=reviewer_name]');
      if (first) first.focus();
    }
  }

  if (openReview) {
    openReview.addEventListener('click', function () {
      showTab('reviews');
      toggleReviewForm(reviewForm && reviewForm.hidden);
    });
  }

  if (cancelReview) {
    cancelReview.addEventListener('click', function () { toggleReviewForm(false); });
  }

  /* ================= quantity ================= */

  var qty = document.getElementById('qty');

  function currentMax() {
    return active ? active.stock : null;
  }

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

  /* ================= variants ================= */

  var dataEl = document.getElementById('variantData');
  var variants = dataEl ? JSON.parse(dataEl.textContent || '[]') : [];
  var stringsEl = document.getElementById('pdpStrings');
  var strings = stringsEl ? JSON.parse(stringsEl.textContent || '{}') : {};

  var chosen = {};
  var active = null;

  if (!variants.length) return;

  var priceMain = document.getElementById('priceMain');
  var priceWas  = document.getElementById('priceWas');
  var priceSave = document.getElementById('priceSave');
  var stockLine = document.getElementById('stockLine');
  var skuLabel  = document.getElementById('skuLabel');
  var addBtn    = document.getElementById('addToCart');
  var buyBtn    = document.getElementById('buyNow');
  var variantField = document.getElementById('variantId');

  var axisCount = document.querySelectorAll('.pp-axis').length;

  function taka(n) {
    return '৳' + Number(n).toLocaleString('en-IN');
  }

  function setStock(state, text) {
    if (!stockLine) return;
    stockLine.className = 'pp-stock ' + state;
    stockLine.innerHTML = '<span class="dot"></span>' + text;
  }

  function setLabel(button, text) {
    if (!button) return;
    var span = button.querySelector('span');
    if (span) span.textContent = text; else button.textContent = text;
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
    // Grey out combinations that do not exist rather than letting someone
    // pick a pair we never stocked.
    document.querySelectorAll('.pp-axis').forEach(function (block) {
      var axisId = block.dataset.axis;
      var reachable = reachableValues(axisId);

      block.querySelectorAll('.pp-opt').forEach(function (btn) {
        btn.classList.toggle('dead', !reachable[btn.dataset.value]);
        btn.classList.toggle('on', String(chosen[axisId]) === btn.dataset.value);
      });
    });

    active = findVariant();

    if (!active) {
      if (variantField) variantField.value = '';
      setStock('muted', strings.choose || '');
      if (addBtn) addBtn.disabled = true;
      if (buyBtn) buyBtn.disabled = true;
      return;
    }

    if (variantField) variantField.value = active.id;
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

    if (active.stock <= 0) {
      setStock('out', strings.outStock || '');
    } else if (active.stock <= 5) {
      setStock('low', (strings.lowStock || '').replace(':n', active.stock));
    } else {
      setStock('ok', strings.inStock || '');
    }

    var soldOut = active.stock <= 0;

    if (addBtn) {
      addBtn.disabled = soldOut;
      setLabel(addBtn, soldOut ? (strings.soldOut || 'Sold out') : (strings.addToCart || 'Add to cart'));
    }
    if (buyBtn) buyBtn.disabled = soldOut;

    clampQty(active.stock);
  }

  document.querySelectorAll('.pp-opt').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (btn.classList.contains('dead')) return;

      var axisId = btn.dataset.axis;

      if (String(chosen[axisId]) === btn.dataset.value) {
        delete chosen[axisId];
      } else {
        chosen[axisId] = btn.dataset.value;
      }

      render();
    });
  });

  // One variant means there is nothing to choose.
  if (variants.length === 1) {
    document.querySelectorAll('.pp-opt').forEach(function (btn) {
      chosen[btn.dataset.axis] = btn.dataset.value;
    });
  }

  render();
})();
