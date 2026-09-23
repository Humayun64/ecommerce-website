/* Editor toolbar and the SEO / readability tab switch. */
(function () {
  'use strict';

  var content = document.getElementById('content');

  document.querySelectorAll('.editor-toolbar button').forEach(function (button) {
    button.addEventListener('click', function () {
      if (!content) return;

      var start = content.selectionStart;
      var end = content.selectionEnd;
      var selected = content.value.slice(start, end);

      if (button.dataset.wrap) {
        var mark = button.dataset.wrap;
        var replacement = mark + (selected || 'text') + mark;
        content.setRangeText(replacement, start, end, 'end');
      } else {
        var insert = button.dataset.insert;
        // Start markers belong at the beginning of their own line.
        var atLineStart = start === 0 || content.value.charAt(start - 1) === '\n';
        var prefix = atLineStart ? '' : '\n';
        content.setRangeText(prefix + insert + selected, start, end, 'end');
      }

      content.focus();
      content.dispatchEvent(new Event('input', { bubbles: true }));
    });
  });

  document.querySelectorAll('[data-seotab]').forEach(function (tab) {
    tab.addEventListener('click', function () {
      document.querySelectorAll('[data-seotab]').forEach(function (t) {
        t.classList.toggle('on', t === tab);
        var panel = document.getElementById(t.dataset.seotab);
        if (panel) panel.style.display = t === tab ? '' : 'none';
      });
    });
  });
})();
