/**
 * Fires the shop events to whichever pixels are switched on.
 *
 * PageView is already fired by the base snippets. This adds the four that
 * depend on what is on the page: the page itself drops a JSON block saying
 * what happened, and this reads it. AddToCart has no page of its own, so it
 * is caught from the form submit instead.
 */
(function () {
  var cfg = window.amjrTrack || {};

  function meta(event, data) {
    if (cfg.fb && window.fbq) window.fbq('track', event, data || {});
  }

  function google(event, data) {
    if ((cfg.ga4 || cfg.ads) && window.gtag) window.gtag('event', event, data || {});
  }

  function tiktok(event, data) {
    if (cfg.tt && window.ttq) window.ttq.track(event, data || {});
  }

  /* Meta, GA4 and TikTok each have their own name for the same thing. */
  var NAMES = {
    ViewContent:      { ga: 'view_item',        tt: 'ViewContent' },
    AddToCart:        { ga: 'add_to_cart',      tt: 'AddToCart' },
    InitiateCheckout: { ga: 'begin_checkout',   tt: 'InitiateCheckout' },
    Purchase:         { ga: 'purchase',         tt: 'CompletePayment' }
  };

  function fire(event, payload) {
    payload = payload || {};

    var names = NAMES[event] || {};
    var currency = payload.currency || 'BDT';
    var value = payload.value;
    var ids = payload.ids || [];

    meta(event, {
      content_ids: ids,
      content_type: 'product',
      content_name: payload.name,
      value: value,
      currency: currency,
      num_items: payload.quantity
    });

    if (names.ga) {
      var gaData = { currency: currency, value: value };

      if (payload.items) gaData.items = payload.items;
      if (event === 'Purchase' && payload.order) gaData.transaction_id = payload.order;
      if (event === 'Purchase') {
        gaData.shipping = payload.shipping;
        gaData.coupon = payload.coupon;
      }

      google(names.ga, gaData);
    }

    /* A Google Ads conversion only counts when a label is set for it. */
    if (event === 'Purchase' && cfg.ads && cfg.label && window.gtag) {
      window.gtag('event', 'conversion', {
        send_to: cfg.ads + '/' + cfg.label,
        value: value,
        currency: currency,
        transaction_id: payload.order
      });
    }

    if (names.tt) {
      tiktok(names.tt, {
        content_id: ids[0],
        content_type: 'product',
        content_name: payload.name,
        quantity: payload.quantity,
        value: value,
        currency: currency
      });
    }
  }

  window.amjrFire = fire;

  /* ---- what this page is ---- */

  var tag = document.getElementById('amjrEvent');

  if (tag) {
    try {
      var data = JSON.parse(tag.textContent || '{}');
      if (data.event) fire(data.event, data);
    } catch (e) {
      /* A malformed block must never take the page down with it. */
    }
  }

  /* ---- add to cart has no page of its own ---- */

  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!form || !form.getAttribute) return;

    var action = form.getAttribute('action') || '';
    if (!/\/cart$/.test(action.split('?')[0])) return;

    var read = function (name) {
      var field = form.querySelector('[name="' + name + '"]');
      return field ? field.value : undefined;
    };

    fire('AddToCart', {
      ids: [read('product_id')].filter(Boolean),
      name: form.getAttribute('data-name') || undefined,
      value: parseFloat(form.getAttribute('data-value')) || undefined,
      quantity: parseInt(read('quantity'), 10) || 1
    });
  }, true);
})();
