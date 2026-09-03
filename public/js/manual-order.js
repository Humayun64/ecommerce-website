/* Manual order form: product lines with live prices and a running total. */
(function () {
  'use strict';

  var dataEl = document.getElementById('productOptions');
  if (!dataEl) return;

  var options = JSON.parse(dataEl.textContent || '[]');
  var rows    = document.getElementById('lineRows');
  var addBtn  = document.getElementById('addLine');
  var totalEl = document.getElementById('lineTotal');
  var index   = 0;

  function optionHtml() {
    return '<option value="">Choose a product…</option>' + options.map(function (o) {
      var label = o.label + (o.stock > 0 ? ' (' + o.stock + ' in stock)' : ' — out of stock');
      return '<option value="' + o.value + '" data-price="' + o.price + '" data-stock="' + o.stock + '"' +
             (o.stock < 1 ? ' disabled' : '') + '>' + label + '</option>';
    }).join('');
  }

  function taka(n) {
    return '\u09F3' + Number(n).toLocaleString('en-IN');
  }

  function retotal() {
    var sum = 0;

    rows.querySelectorAll('tr').forEach(function (tr) {
      var qty = parseFloat(tr.querySelector('.lqty').value) || 0;
      var price = parseFloat(tr.querySelector('.lprice').value) || 0;
      sum += qty * price;
    });

    if (totalEl) {
      totalEl.textContent = sum > 0 ? 'Products subtotal: ' + taka(sum) : '';
    }
  }

  function addRow() {
    var i = index++;
    var tr = document.createElement('tr');

    tr.innerHTML =
      '<td><select name="lines[' + i + '][picker]" class="lpick">' + optionHtml() + '</select></td>' +
      '<td><input type="number" min="1" value="1" name="lines[' + i + '][quantity]" class="lqty"></td>' +
      '<td><input type="number" step="0.01" min="0" name="lines[' + i + '][unit_price]" class="lprice"></td>' +
      '<td><button type="button" class="btn btn-danger btn-sm lremove">Remove</button></td>';

    rows.appendChild(tr);

    var pick = tr.querySelector('.lpick');

    // Picking a product fills in its current price, which you can then override
    // for the discounts you agree over Messenger.
    pick.addEventListener('change', function () {
      var opt = pick.options[pick.selectedIndex];
      var price = opt ? opt.dataset.price : '';
      var stock = opt ? parseInt(opt.dataset.stock, 10) : null;

      if (price) tr.querySelector('.lprice').value = price;
      if (stock) tr.querySelector('.lqty').max = stock;

      retotal();
    });

    tr.querySelector('.lqty').addEventListener('input', retotal);
    tr.querySelector('.lprice').addEventListener('input', retotal);
  }

  rows.addEventListener('click', function (e) {
    if (!e.target.classList.contains('lremove')) return;
    e.target.closest('tr').remove();
    retotal();
  });

  if (addBtn) addBtn.addEventListener('click', addRow);

  addRow();
})();
