/* Product form: variation matrix, margin readout, live SEO preview. */
(function () {
  'use strict';

  /* ================= variation matrix ================= */

  var rows      = document.getElementById('variantRows');
  var genBtn    = document.getElementById('generateMatrix');
  var template  = document.getElementById('variantTemplate');
  var emptyNote = document.getElementById('noVariants');
  var stockBox  = document.getElementById('simpleStock');
  var varNote   = document.getElementById('variantStockNote');
  var hint      = document.getElementById('matrixHint');

  function variantCount() {
    return rows ? rows.querySelectorAll('tr').length : 0;
  }

  function refreshState() {
    var count = variantCount();
    if (emptyNote) emptyNote.style.display = count ? 'none' : 'block';
    if (stockBox)  stockBox.style.display  = count ? 'none' : 'grid';
    if (varNote)   varNote.style.display   = count ? 'block' : 'none';
  }

  /* --- axis enable/disable --- */

  function syncAxis(box) {
    var panel = box.closest('.axis');
    if (!panel) return;

    panel.classList.toggle('on', box.checked);
    panel.querySelectorAll('.valueToggle').forEach(function (v) {
      v.disabled = !box.checked;
      if (!box.checked) v.checked = false;
    });
  }

  document.querySelectorAll('.axisToggle').forEach(function (box) {
    syncAxis(box);
    box.addEventListener('change', function () {
      var on = document.querySelectorAll('.axisToggle:checked');
      if (on.length > 2 && box.checked) {
        box.checked = false;
        alert('Two attributes maximum. More than that and the grid becomes unmanageable.');
      }
      syncAxis(box);
    });
  });

  /* --- selected values, grouped by axis --- */

  function selectedGroups() {
    var groups = {};

    document.querySelectorAll('.valueToggle:checked').forEach(function (box) {
      var axis = box.dataset.axis;
      if (!groups[axis]) groups[axis] = [];
      groups[axis].push({ id: box.value, label: box.dataset.label });
    });

    return Object.keys(groups).map(function (k) { return groups[k]; });
  }

  function cartesian(groups) {
    return groups.reduce(function (acc, group) {
      var out = [];
      acc.forEach(function (combo) {
        group.forEach(function (item) {
          out.push(combo.concat([item]));
        });
      });
      return out;
    }, [[]]);
  }

  /* --- pre-tick values already used by existing rows --- */

  (function preselect() {
    if (!rows) return;
    var used = {};

    rows.querySelectorAll('input[name*="[value_ids]"]').forEach(function (input) {
      used[input.value] = true;
    });

    document.querySelectorAll('.valueToggle').forEach(function (box) {
      if (used[box.value] && !box.disabled) box.checked = true;
    });
  })();

  if (genBtn) {
    genBtn.addEventListener('click', function () {
      var groups = selectedGroups();

      if (!groups.length) {
        if (hint) hint.textContent = 'Tick at least one value first.';
        return;
      }

      var combos = cartesian(groups);

      if (combos.length > 40) {
        alert('That would make ' + combos.length + ' variations. Narrow it down — anything past about 20 stops being maintainable.');
        return;
      }

      // Keep the data already typed against each combination.
      var existing = {};
      rows.querySelectorAll('tr').forEach(function (tr) {
        existing[tr.dataset.key] = tr;
      });

      var fresh = document.createDocumentFragment();
      var added = 0;

      combos.forEach(function (combo, index) {
        var ids   = combo.map(function (c) { return c.id; }).sort();
        var key   = ids.join('-');
        var label = combo.map(function (c) { return c.label; }).join(' / ');

        if (existing[key]) {
          fresh.appendChild(existing[key]);
          delete existing[key];
          return;
        }

        var inputs = ids.map(function (id) {
          return '<input type="hidden" name="variants[' + index + '][value_ids][]" value="' + id + '">';
        }).join('');

        var html = template.innerHTML
          .replace(/__key__/g, key)
          .replace(/__label__/g, label)
          .replace(/__valueinputs__/g, inputs)
          .replace(/__i__/g, index);

        var host = document.createElement('tbody');
        host.innerHTML = html.trim();
        fresh.appendChild(host.firstChild);
        added++;
      });

      rows.innerHTML = '';
      rows.appendChild(fresh);
      reindex();
      refreshState();

      if (hint) {
        hint.textContent = combos.length + ' variation' + (combos.length > 1 ? 's' : '') +
          (added ? ', ' + added + ' new' : ', all kept');
      }
    });
  }

  /* --- renumber variants[N] after any change --- */

  function reindex() {
    if (!rows) return;
    rows.querySelectorAll('tr').forEach(function (tr, i) {
      tr.querySelectorAll('input').forEach(function (input) {
        if (input.name) {
          input.name = input.name.replace(/variants\[\d+\]/, 'variants[' + i + ']');
        }
      });
    });
  }

  if (rows) {
    rows.addEventListener('click', function (e) {
      if (!e.target.classList.contains('removeVariant')) return;
      e.target.closest('tr').remove();
      reindex();
      refreshState();
    });
  }

  refreshState();

  /* ================= margin readout ================= */

  var price   = document.getElementById('price');
  var cost    = document.getElementById('cost_price');
  var readout = document.getElementById('marginReadout');

  function showMargin() {
    if (!readout) return;

    var p = parseFloat(price && price.value);
    var c = parseFloat(cost && cost.value);

    if (isNaN(p) || isNaN(c) || p <= 0) {
      readout.textContent = '';
      return;
    }

    var profit = p - c;
    var margin = Math.round((profit / p) * 100);

    readout.textContent = 'Profit \u09F3' + profit.toFixed(0) + ' per unit \u2014 ' + margin + '% margin';
    readout.style.color = profit <= 0 ? '#B3261E' : (margin < 15 ? '#B4530C' : '#1B7F4C');
  }

  if (price) price.addEventListener('input', showMargin);
  if (cost)  cost.addEventListener('input', showMargin);
  showMargin();

  /* ================= live SEO preview ================= */

  var name = document.getElementById('name');
  var slug = document.getElementById('slug');
  var mt   = document.getElementById('meta_title');
  var md   = document.getElementById('meta_description');

  var serpTitle = document.getElementById('serpTitle');
  var serpDesc  = document.getElementById('serpDesc');
  var serpSlug  = document.getElementById('serpSlug');
  var mtCount   = document.getElementById('mtCount');
  var mdCount   = document.getElementById('mdCount');

  function slugify(text) {
    return text.toLowerCase().trim()
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-');
  }

  function counter(el, value, ideal, max) {
    if (!el) return;
    var n = value.length;
    el.textContent = n + ' / ' + max;
    el.style.color = n === 0 ? '#5B6270' : (n < ideal ? '#B4530C' : '#1B7F4C');
  }

  function refreshSeo() {
    var nameVal = name ? name.value : '';
    var mtVal   = mt ? mt.value : '';
    var mdVal   = md ? md.value : '';

    if (serpTitle) serpTitle.textContent = mtVal || nameVal || 'Product name';
    if (serpDesc && mdVal) serpDesc.textContent = mdVal;
    if (serpSlug) serpSlug.textContent = (slug && slug.value) || slugify(nameVal) || 'product-name';

    counter(mtCount, mtVal, 50, 70);
    counter(mdCount, mdVal, 140, 180);
  }

  [name, slug, mt, md].forEach(function (el) {
    if (el) el.addEventListener('input', refreshSeo);
  });
  refreshSeo();
})();
