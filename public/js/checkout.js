/* Checkout: live delivery charge and total as the area is chosen. */
(function () {
  'use strict';

  var dataEl = document.getElementById('checkoutData');
  if (!dataEl) return;

  var data = JSON.parse(dataEl.textContent || '{}');
  var deliveryCell = document.getElementById('deliveryCell');
  var totalCell = document.getElementById('totalCell');
  var form = document.getElementById('checkoutForm');
  var submit = document.getElementById('placeOrder');

  function taka(n) {
    return '\u09F3' + Number(n).toLocaleString('en-IN');
  }

  function update() {
    var picked = document.querySelector('input[name=shipping_zone_id]:checked');
    var free = data.subtotal >= data.freeOver && data.freeOver > 0;

    document.querySelectorAll('.zonelist .zone-opt').forEach(function (label) {
      label.classList.toggle('selected', label.contains(picked));
    });

    if (free) {
      if (deliveryCell) deliveryCell.textContent = data.free;
      if (totalCell) totalCell.textContent = taka(data.subtotal);
      return;
    }

    if (!picked) {
      if (deliveryCell) deliveryCell.textContent = data.choose;
      if (totalCell) totalCell.textContent = taka(data.subtotal);
      return;
    }

    var rate = parseFloat(picked.dataset.rate) || 0;
    if (deliveryCell) deliveryCell.textContent = taka(rate);
    if (totalCell) totalCell.textContent = taka(data.subtotal + rate);
  }

  document.querySelectorAll('input[name=shipping_zone_id]').forEach(function (radio) {
    radio.addEventListener('change', update);
  });

  // Placing an order is not idempotent, so block the double click.
  if (form && submit) {
    form.addEventListener('submit', function () {
      setTimeout(function () {
        submit.disabled = true;
        submit.textContent = '…';
      }, 0);
    });
  }

  update();
})();
