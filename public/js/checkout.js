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

  var goods = Math.max(0, data.subtotal - (data.discount || 0));

  function taka(n) {
    return '৳' + Number(n).toLocaleString('en-IN');
  }

  function update() {
    var picked = document.querySelector('input[name=shipping_zone_id]:checked');

    // A free-shipping coupon wins; otherwise the spend threshold is
    // measured on the subtotal before any discount.
    var free = data.freeShip || (data.freeOver > 0 && data.subtotal >= data.freeOver);

    document.querySelectorAll('.zonelist .zone-opt').forEach(function (label) {
      label.classList.toggle('selected', label.contains(picked));
    });

    if (free) {
      if (deliveryCell) deliveryCell.textContent = data.free;
      if (totalCell) totalCell.textContent = taka(goods);
      return;
    }

    if (!picked) {
      if (deliveryCell) deliveryCell.textContent = data.choose;
      if (totalCell) totalCell.textContent = taka(goods);
      return;
    }

    var rate = parseFloat(picked.dataset.rate) || 0;
    if (deliveryCell) deliveryCell.textContent = taka(rate);
    if (totalCell) totalCell.textContent = taka(goods + rate);
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
