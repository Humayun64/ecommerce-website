@csrf
@if ($method === 'PUT') @method('PUT') @endif

@if ($errors->any())
  <div class="alert alert-bad">
    <ul style="margin:0;padding-left:18px">
      @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
  </div>
@endif

<div class="form-grid">
<div class="form-main">

  <div class="panel">
    <div class="panel-head"><h2>Content</h2></div>
    <div class="panel-body">

      <div class="field">
        <label for="title">Page title</label>
        <input type="text" id="title" name="title" value="{{ old('title', $page->title) }}" required>
      </div>

      <div class="field">
        <label for="slug">Address</label>
        <div style="display:flex;align-items:center;gap:6px">
          <span class="sub" style="white-space:nowrap">{{ url('/') }}/</span>
          <input type="text" id="slug" name="slug" value="{{ old('slug', $page->slug) }}" placeholder="leave blank to generate">
        </div>
        <div class="hint">Lowercase letters, numbers and dashes. Changing it on a live page breaks existing links.</div>
      </div>

      <div class="field">
        <label for="excerpt">One-line summary</label>
        <input type="text" id="excerpt" name="excerpt" value="{{ old('excerpt', $page->excerpt) }}"
               placeholder="Shown in search results if you leave the meta description empty.">
      </div>

      <div class="field">
        <label for="content">Page content</label>
        <textarea id="content" name="content" style="min-height:420px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:13.5px;line-height:1.6">{{ old('content', $page->content) }}</textarea>
        <div class="hint">
          Blank line between paragraphs. <code>## Heading</code> for a heading,
          <code>- item</code> for a bullet, <code>**bold**</code> for bold,
          <code>[text](/link)</code> for a link. Anything else is shown as plain text.
        </div>
      </div>

    </div>
  </div>

  <div class="panel">
    <div class="panel-head"><h2>Search</h2></div>
    <div class="panel-body">
      <div class="field">
        <label for="meta_title">Meta title</label>
        <input type="text" id="meta_title" name="meta_title" maxlength="70" value="{{ old('meta_title', $page->meta_title) }}"
               placeholder="{{ $page->title ?: 'Falls back to the page title' }}">
      </div>
      <div class="field">
        <label for="meta_description">Meta description</label>
        <textarea id="meta_description" name="meta_description" maxlength="180" style="min-height:66px">{{ old('meta_description', $page->meta_description) }}</textarea>
      </div>
      <div class="field" style="margin:0">
        <label class="check">
          <input type="checkbox" name="is_indexable" value="1" @checked(old('is_indexable', $page->is_indexable ?? true))>
          Let search engines index this page
        </label>
      </div>
    </div>
  </div>

</div>

<div class="form-side">
  <div class="panel">
    <div class="panel-head"><h2>Publish</h2></div>
    <div class="panel-body">
      <div class="field">
        <label class="check">
          <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $page->is_published ?? true))>
          Live on the site
        </label>
        <div class="hint">Untick to keep working on it privately.</div>
      </div>
      <div class="field">
        <label for="sort_order">Order</label>
        <input type="number" id="sort_order" name="sort_order" min="0" value="{{ old('sort_order', $page->sort_order ?? 0) }}">
      </div>
      <button type="submit" class="btn btn-gold" style="width:100%">{{ $submit }}</button>
      <a href="{{ route('admin.pages.index') }}" class="btn btn-line" style="width:100%;margin-top:9px">Cancel</a>
      @if ($page->exists)
        <a href="{{ url('/' . $page->slug) }}" target="_blank" class="btn btn-line" style="width:100%;margin-top:9px">View on site</a>
      @endif
    </div>
  </div>

  <div class="panel">
    <div class="panel-head"><h2>Next step</h2></div>
    <div class="panel-body">
      <p class="sub" style="margin:0 0 12px">
        A page is not linked from anywhere until you add it to a menu.
      </p>
      <a href="{{ route('admin.menus.index', ['location' => 'footer']) }}" class="btn btn-navy btn-sm" style="width:100%">Open the footer menu</a>
    </div>
  </div>
</div>
</div>
