/**
 * Checkout payment picker.
 *
 * Shows the panel belonging to the chosen method and, just as importantly,
 * disables the fields in every other panel: a disabled input is not posted,
 * so an unpicked method cannot send its transaction ID along with the order,
 * and a hidden required field cannot block the form from submitting.
 */
(function () {
  var radios = document.querySelectorAll('input[name="payment_method_code"]');
  if (!radios.length) return;

  function sync() {
    radios.forEach(function (radio) {
      var panel = document.getElementById('pm-panel-' + radio.value);
      if (!panel) return;

      var on = radio.checked;
      panel.hidden = !on;

      panel.querySelectorAll('input, select, textarea').forEach(function (field) {
        field.disabled = !on;
      });
    });
  }

  radios.forEach(function (radio) {
    radio.addEventListener('change', sync);
  });

  sync();

  /* ---- copy the number to the clipboard ---- */

  document.querySelectorAll('.pm-copy').forEach(function (button) {
    var label = button.querySelector('span');
    if (!label) return;

    button.addEventListener('click', function () {
      var number = button.getAttribute('data-copy') || '';
      var original = label.textContent;

      function done() {
        button.classList.add('done');
        label.textContent = 'Copied';
        setTimeout(function () {
          label.textContent = original;
          button.classList.remove('done');
        }, 1400);
      }

      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(number).then(done, fallback);
        return;
      }

      fallback();

      // http://localhost has no clipboard API, and that is where this is built.
      function fallback() {
        var scratch = document.createElement('textarea');
        scratch.value = number;
        scratch.setAttribute('readonly', '');
        scratch.style.position = 'fixed';
        scratch.style.top = '-1000px';
        document.body.appendChild(scratch);
        scratch.select();

        try {
          document.execCommand('copy');
          done();
        } catch (e) {
          /* Nothing we can do — the number is on screen to read. */
        }

        document.body.removeChild(scratch);
      }
    });
  });
})();
