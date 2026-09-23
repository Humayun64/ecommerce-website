/* Menu manager: drag to reorder, inline edit, URL suggestions. */
(function () {
  'use strict';

  var token = document.querySelector('meta[name="csrf-token"]');

  /* ---------- inline edit ---------- */

  document.addEventListener('click', function (e) {
    var trigger = e.target.closest('[data-toggle]');
    if (!trigger) return;

    var panel = document.getElementById(trigger.dataset.toggle);
    if (panel) panel.classList.toggle('open');
  });

  /* ---------- url suggestions ---------- */

  document.querySelectorAll('.mn-sugg button').forEach(function (button) {
    button.addEventListener('click', function () {
      var url = document.getElementById('url');
      var label = document.getElementById('label');

      if (url) url.value = button.dataset.url;
      if (label && !label.value && button.dataset.label) label.value = button.dataset.label;
    });
  });

  /* ---------- drag to reorder ---------- */

  document.querySelectorAll('[data-sortable]').forEach(function (list) {
    var dragging = null;

    list.addEventListener('dragstart', function (e) {
      dragging = e.target.closest('.mn-item');
      if (!dragging) return;
      dragging.classList.add('dragging');
      e.dataTransfer.effectAllowed = 'move';
      // Firefox will not start a drag without data set.
      e.dataTransfer.setData('text/plain', dragging.dataset.id);
    });

    list.addEventListener('dragover', function (e) {
      e.preventDefault();
      var over = e.target.closest('.mn-item');
      if (!over || !dragging || over === dragging) return;

      list.querySelectorAll('.mn-item').forEach(function (i) { i.classList.remove('over'); });
      over.classList.add('over');

      var box = over.getBoundingClientRect();
      var after = (e.clientY - box.top) > box.height / 2;

      list.insertBefore(dragging, after ? over.nextSibling : over);
    });

    list.addEventListener('dragend', function () {
      if (!dragging) return;
      dragging.classList.remove('dragging');
      list.querySelectorAll('.mn-item').forEach(function (i) { i.classList.remove('over'); });
      dragging = null;
      renumber(list);
      save(list);
    });
  });

  function renumber(list) {
    list.querySelectorAll('.mn-item').forEach(function (item, i) {
      var badge = item.querySelector('.mn-pos');
      if (badge) badge.textContent = i + 1;
    });
  }

  function save(list) {
    var order = Array.prototype.map.call(
      list.querySelectorAll('.mn-item'),
      function (item) { return item.dataset.id; }
    );

    if (!window.fetch || !token) { window.location.reload(); return; }

    fetch(list.dataset.url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token.content,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ order: order })
    }).catch(function () {
      // If the save failed the page is now lying about the order.
      window.location.reload();
    });
  }
})();
