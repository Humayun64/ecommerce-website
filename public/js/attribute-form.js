/* Attribute form: repeatable value rows. */
(function () {
  'use strict';

  var rows = document.getElementById('valueRows');
  var add  = document.getElementById('addValue');
  var tpl  = document.getElementById('valueTemplate');

  if (add && rows && tpl) {
    add.addEventListener('click', function () {
      var host = document.createElement('tbody');
      host.innerHTML = tpl.innerHTML.replace(/__i__/g, Date.now()).trim();
      rows.appendChild(host.firstChild);
      host.querySelectorAll('input[type=text]').forEach(function () {});
      var last = rows.lastElementChild.querySelector('input[type=text]');
      if (last) last.focus();
    });
  }

  if (rows) {
    rows.addEventListener('click', function (e) {
      if (e.target.classList.contains('removeValue')) {
        e.target.closest('tr').remove();
      }
    });
  }
})();
