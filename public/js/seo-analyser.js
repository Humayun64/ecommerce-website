/* Live SEO and readability analysis for the post editor.
   Everything runs in the browser as you type — nothing is sent anywhere. */
(function () {
  'use strict';

  var el = function (id) { return document.getElementById(id); };

  var fields = {
    title:   el('title'),
    slug:    el('slug'),
    metaT:   el('meta_title'),
    metaD:   el('meta_description'),
    keyword: el('focus_keyword'),
    content: el('content'),
    alt:     el('cover_alt')
  };

  var out = {
    seo:      el('seoChecks'),
    read:     el('readChecks'),
    score:    el('seoScore'),
    label:    el('seoScoreLabel'),
    words:    el('wordCount'),
    minutes:  el('readMinutes'),
    serpT:    el('serpTitle'),
    serpD:    el('serpDesc'),
    serpU:    el('serpUrl'),
    mtCount:  el('mtCount'),
    mdCount:  el('mdCount')
  };

  if (!fields.title || !out.seo) return;

  var siteRoot = out.serpU ? out.serpU.dataset.root : '';

  /* ---------- helpers ---------- */

  function val(field) { return field ? field.value.trim() : ''; }

  function slugify(text) {
    return text.toLowerCase().trim()
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-');
  }

  /** Content with the mark-up stripped, so counts reflect real prose. */
  function plain(text) {
    return text
      .replace(/!\[[^\]]*\]\([^)]*\)/g, ' ')
      .replace(/\[([^\]]+)\]\([^)]*\)/g, '$1')
      .replace(/^[#>\-\s]+/gm, '')
      .replace(/\*\*?/g, '')
      .replace(/\s+/g, ' ')
      .trim();
  }

  function words(text) {
    return text ? text.split(/\s+/).filter(Boolean) : [];
  }

  function countOccurrences(haystack, needle) {
    if (!needle) return 0;
    var escaped = needle.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    var matches = haystack.toLowerCase().match(new RegExp(escaped.toLowerCase(), 'g'));
    return matches ? matches.length : 0;
  }

  function has(haystack, needle) {
    return needle !== '' && haystack.toLowerCase().indexOf(needle.toLowerCase()) !== -1;
  }

  /* ---------- the checks ---------- */

  function analyse() {
    var title   = val(fields.title);
    var keyword = val(fields.keyword);
    var metaT   = val(fields.metaT) || title;
    var metaD   = val(fields.metaD);
    var slug    = val(fields.slug) || slugify(title);
    var raw     = val(fields.content);
    var body    = plain(raw);
    var wordList = words(body);
    var wordCount = wordList.length;

    var seo = [];
    var read = [];

    /* --- keyword --- */

    if (!keyword) {
      seo.push(['bad', 'No focus keyword set. Pick the phrase you want this post to rank for.']);
    } else {
      seo.push(['good', 'Focus keyword set: “' + keyword + '”.']);

      var inTitle = has(title, keyword);
      seo.push(inTitle
        ? (has(title.slice(0, Math.ceil(title.length / 2)), keyword)
            ? ['good', 'Keyword appears early in the title.']
            : ['ok', 'Keyword is in the title, but late. Earlier reads stronger.'])
        : ['bad', 'Keyword is not in the title.']);

      seo.push(has(slug, slugify(keyword))
        ? ['good', 'Keyword is in the address.']
        : ['ok', 'Keyword is not in the address.']);

      seo.push(has(metaD, keyword)
        ? ['good', 'Keyword is in the meta description.']
        : ['bad', 'Keyword is missing from the meta description.']);

      var firstPara = raw.split(/\n\s*\n/)[0] || '';
      seo.push(has(plain(firstPara), keyword)
        ? ['good', 'Keyword appears in the opening paragraph.']
        : ['bad', 'Keyword is not in the opening paragraph.']);

      var headings = (raw.match(/^##+\s+.*$/gm) || []).join(' ');
      seo.push(has(headings, keyword)
        ? ['good', 'Keyword appears in a subheading.']
        : ['ok', 'No subheading contains the keyword.']);

      if (wordCount > 0) {
        var density = (countOccurrences(body, keyword) / wordCount) * 100;
        var shown = density.toFixed(1) + '%';

        if (density === 0) {
          seo.push(['bad', 'Keyword never appears in the body.']);
        } else if (density < 0.5) {
          seo.push(['ok', 'Keyword density is ' + shown + ' — a little thin.']);
        } else if (density <= 2.5) {
          seo.push(['good', 'Keyword density is ' + shown + ' — about right.']);
        } else {
          seo.push(['bad', 'Keyword density is ' + shown + ' — that reads as stuffing.']);
        }
      }
    }

    /* --- title and description --- */

    var tLen = metaT.length;
    seo.push(tLen === 0 ? ['bad', 'No title yet.']
      : tLen < 30 ? ['ok', 'Title is ' + tLen + ' characters — short. Aim for 50 to 60.']
      : tLen <= 60 ? ['good', 'Title length is ' + tLen + ' characters.']
      : ['bad', 'Title is ' + tLen + ' characters — Google will cut it off.']);

    var dLen = metaD.length;
    seo.push(dLen === 0 ? ['bad', 'No meta description. Google will pick a sentence for you.']
      : dLen < 120 ? ['ok', 'Meta description is ' + dLen + ' characters — room for more.']
      : dLen <= 160 ? ['good', 'Meta description length is ' + dLen + ' characters.']
      : ['bad', 'Meta description is ' + dLen + ' characters — it will be truncated.']);

    /* --- body --- */

    seo.push(wordCount === 0 ? ['bad', 'Nothing written yet.']
      : wordCount < 300 ? ['bad', wordCount + ' words. Under 300 rarely ranks.']
      : wordCount < 600 ? ['ok', wordCount + ' words. Fine, but 600+ does better.']
      : ['good', wordCount + ' words.']);

    var links = raw.match(/\[[^\]]+\]\(([^)\s]+)\)/g) || [];
    var internal = 0, external = 0;

    links.forEach(function (link) {
      var url = link.replace(/^.*\(/, '').replace(/\)$/, '');
      if (/^https?:\/\//i.test(url)) external++; else if (url.charAt(0) === '/') internal++;
    });

    seo.push(internal > 0
      ? ['good', internal + ' internal link' + (internal === 1 ? '' : 's') + ' — good for the rest of the site.']
      : ['bad', 'No internal links. Link to a product or another post.']);

    seo.push(external > 0
      ? ['good', external + ' outbound link' + (external === 1 ? '' : 's') + '.']
      : ['ok', 'No outbound links. One to a brand or source adds credibility.']);

    var altText = val(fields.alt);
    seo.push(!altText ? ['bad', 'Cover image has no alt text.']
      : (keyword && has(altText, keyword)) ? ['good', 'Cover alt text includes the keyword.']
      : ['ok', 'Cover alt text is set but does not mention the keyword.']);

    /* --- readability --- */

    var sentences = body.split(/[.!?]+\s/).filter(function (s) { return s.trim().length > 0; });
    var avgSentence = sentences.length ? wordCount / sentences.length : 0;

    read.push(avgSentence === 0 ? ['bad', 'Nothing to read yet.']
      : avgSentence <= 20 ? ['good', 'Sentences average ' + avgSentence.toFixed(0) + ' words.']
      : avgSentence <= 25 ? ['ok', 'Sentences average ' + avgSentence.toFixed(0) + ' words — getting long.']
      : ['bad', 'Sentences average ' + avgSentence.toFixed(0) + ' words. Break them up.']);

    var paragraphs = raw.split(/\n\s*\n/).filter(function (p) { return p.trim() && !/^[#>\-!]/.test(p.trim()); });
    var longParas = paragraphs.filter(function (p) { return words(plain(p)).length > 150; }).length;

    read.push(longParas === 0
      ? ['good', 'No overlong paragraphs.']
      : ['ok', longParas + ' paragraph' + (longParas === 1 ? ' is' : 's are') + ' over 150 words.']);

    var headingCount = (raw.match(/^##+\s+/gm) || []).length;

    read.push(wordCount < 300 ? ['ok', 'Too short to need subheadings yet.']
      : headingCount === 0 ? ['bad', 'No subheadings. Long text without them is hard to scan.']
      : (wordCount / (headingCount + 1)) > 300
        ? ['ok', 'Some sections run over 300 words without a heading.']
        : ['good', headingCount + ' subheading' + (headingCount === 1 ? '' : 's') + ', well spread.']);

    var hasList = /^-\s+/m.test(raw);
    var hasImage = /!\[[^\]]*\]\(/.test(raw);

    read.push((hasList || hasImage)
      ? ['good', 'Uses ' + [hasList ? 'lists' : null, hasImage ? 'images' : null].filter(Boolean).join(' and ') + '.']
      : ['ok', 'No lists or images. Both break up a wall of text.']);

    paint(seo, read, wordCount, metaT, metaD, slug);
  }

  /* ---------- rendering ---------- */

  function paint(seo, read, wordCount, metaT, metaD, slug) {
    out.seo.innerHTML = seo.map(row).join('');
    out.read.innerHTML = read.map(row).join('');

    var all = seo.concat(read);
    var good = all.filter(function (c) { return c[0] === 'good'; }).length;
    var bad = all.filter(function (c) { return c[0] === 'bad'; }).length;
    var score = Math.max(0, Math.round((good / all.length) * 100) - bad * 3);

    if (out.score) {
      out.score.textContent = score;
      out.score.className = 'seo-score ' + (score >= 75 ? 'good' : score >= 45 ? 'ok' : 'bad');
    }

    if (out.label) {
      out.label.textContent = score >= 75 ? 'Good' : score >= 45 ? 'Needs work' : 'Poor';
    }

    if (out.words) out.words.textContent = wordCount;
    if (out.minutes) out.minutes.textContent = Math.max(1, Math.ceil(wordCount / 200));

    if (out.serpT) out.serpT.textContent = metaT || 'Post title';
    if (out.serpD) out.serpD.textContent = metaD || 'Write a meta description so Google shows your words instead of picking a sentence itself.';
    if (out.serpU) out.serpU.textContent = siteRoot + '/blog/' + (slug || 'post-address');

    counter(out.mtCount, metaT.length, 60);
    counter(out.mdCount, metaD.length, 160);
  }

  function counter(node, length, max) {
    if (!node) return;
    node.textContent = length + ' / ' + max;
    node.style.color = length === 0 ? '#5B6270'
      : length > max ? '#B3261E'
      : length > max * 0.7 ? '#1B7F4C' : '#B4530C';
  }

  function row(check) {
    return '<li class="chk ' + check[0] + '"><span class="dot"></span>' + check[1] + '</li>';
  }

  /* ---------- wiring ---------- */

  var timer = null;

  function schedule() {
    clearTimeout(timer);
    timer = setTimeout(analyse, 220);
  }

  Object.keys(fields).forEach(function (key) {
    if (fields[key]) fields[key].addEventListener('input', schedule);
  });

  analyse();
})();
