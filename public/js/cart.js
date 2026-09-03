/* Adds to the cart without a page reload, falling back to a normal
   form post if anything goes wrong. */
(function () {
  'use strict';

  var token = document.querySelector('meta[name="csrf-token"]');
  var badge = document.getElementById('cartCount');

  function setCount(n) {
    if (!badge) return;
    badge.textContent = n;
    badge.style.display = n > 0 ? '' : 'none';
  }

  function toast(message, bad) {
    var el = document.createElement('div');
    el.className = 'toast' + (bad ? ' bad' : '');
    el.textContent = message;
    document.body.appendChild(el);

    requestAnimationFrame(function () { el.classList.add('in'); });

    setTimeout(function () {
      el.classList.remove('in');
      setTimeout(function () { el.remove(); }, 250);
    }, 3200);
  }

  function submitAsync(form) {
    var button = form.querySelector('button[type=submit]');
    var original = button ? button.textContent : '';

    if (button) {
      button.disabled = true;
      button.textContent = '…';
    }

    fetch(form.action, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': token ? token.content : '',
        'Accept': 'application/json'
      },
      body: new FormData(form)
    })
      .then(function (res) { return res.json().then(function (b) { return { ok: res.ok, body: b }; }); })
      .then(function (r) {
        if (r.body && typeof r.body.count !== 'undefined') setCount(r.body.count);
        toast(r.body.message || '', !r.ok);
      })
      .catch(function () {
        // Network or parse failure — let the browser do it the old way.
        form.submit();
      })
      .finally(function () {
        if (button) {
          button.disabled = false;
          button.textContent = original;
        }
      });
  }

  document.addEventListener('submit', function (e) {
    var form = e.target;

    if (!form.matches('.quickadd, #addForm')) return;
    if (!window.fetch || !token) return;   // no JS support, normal post

    e.preventDefault();
    submitAsync(form);
  });
})();
