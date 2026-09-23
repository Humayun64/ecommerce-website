@csrf
@if ($method === 'PUT') @method('PUT') @endif

@if ($errors->any())
  <div class="alert alert-bad">
    <ul style="margin:0;padding-left:18px">
      @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
  </div>
@endif

<style>
.seo-head{display:flex;align-items:center;gap:14px}
.seo-score{width:52px;height:52px;border-radius:50%;display:grid;place-items:center;font-family:var(--display);font-variation-settings:"wdth" 115;font-weight:800;font-size:19px;color:#fff;flex-shrink:0}
.seo-score.good{background:linear-gradient(160deg,#25A464,#1B7F4C)}
.seo-score.ok{background:linear-gradient(160deg,#E0A33A,#B4530C)}
.seo-score.bad{background:linear-gradient(160deg,#D2453C,#B3261E)}
.seo-head b{display:block;font-size:15px}
.seo-head small{display:block;font-size:12.5px;color:var(--ink-mute)}
.chklist{list-style:none;margin:0;padding:0}
.chk{display:flex;gap:9px;align-items:flex-start;font-size:13px;line-height:1.5;padding:7px 0;border-bottom:1px solid var(--line);color:var(--ink-mute)}
.chk:last-child{border-bottom:0}
.chk .dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;margin-top:6px}
.chk.good .dot{background:var(--ok)}
.chk.ok .dot{background:var(--warn)}
.chk.bad .dot{background:var(--bad)}
.chk.good{color:var(--ink)}
.seo-tabs{display:flex;gap:4px;margin-bottom:12px}
.seo-tabs button{border:0;background:var(--paper);border-radius:3px;padding:7px 12px;font:inherit;font-size:13px;font-weight:600;cursor:pointer;color:var(--ink-mute)}
.seo-tabs button.on{background:var(--navy);color:#fff}
.seo-stats{display:flex;gap:16px;font-size:12.5px;color:var(--ink-mute);margin-bottom:12px}
.seo-stats b{color:var(--ink)}
.editor-toolbar{display:flex;gap:5px;flex-wrap:wrap;margin-bottom:8px}
.editor-toolbar button{border:1.5px solid var(--line);background:#fff;border-radius:3px;padding:5px 10px;font:inherit;font-size:12.5px;cursor:pointer;color:var(--ink-mute)}
.editor-toolbar button:hover{border-color:var(--navy);color:var(--navy)}
.cover-preview{width:100%;border-radius:4px;border:1px solid var(--line);margin-bottom:10px;display:block}
</style>

<div class="form-grid">
<div class="form-main">

  <div class="panel">
    <div class="panel-head"><h2>The post</h2></div>
    <div class="panel-body">

      <div class="field">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="{{ old('title', $post->title) }}" required
               placeholder="How to tell a real Anua serum from a fake">
      </div>

      <div class="row2">
        <div class="field">
          <label for="slug">Address</label>
          <input type="text" id="slug" name="slug" value="{{ old('slug', $post->slug) }}" placeholder="leave blank to generate">
          <div class="hint">Lives at /blog/your-address</div>
        </div>
        <div class="field">
          <label for="blog_category_id">Topic</label>
          <select id="blog_category_id" name="blog_category_id">
            <option value="">No topic</option>
            @foreach ($categories as $category)
              <option value="{{ $category->id }}" @selected(old('blog_category_id', $post->blog_category_id) == $category->id)>{{ $category->name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="field">
        <label for="excerpt">Summary</label>
        <textarea id="excerpt" name="excerpt" style="min-height:64px"
                  placeholder="One or two sentences shown on the blog list.">{{ old('excerpt', $post->excerpt) }}</textarea>
      </div>

      <div class="field">
        <label for="content">Content</label>
        <div class="editor-toolbar">
          <button type="button" data-insert="## ">Heading</button>
          <button type="button" data-insert="### ">Small heading</button>
          <button type="button" data-wrap="**">Bold</button>
          <button type="button" data-wrap="*">Italic</button>
          <button type="button" data-insert="- ">Bullet</button>
          <button type="button" data-insert="&gt; ">Quote</button>
          <button type="button" data-insert="[text](/shop)">Link</button>
          <button type="button" data-insert="![describe the image](/storage/blog/file.jpg)">Image</button>
        </div>
        <textarea id="content" name="content" style="min-height:520px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:13.5px;line-height:1.7">{{ old('content', $post->content) }}</textarea>
        <div class="hint">
          Blank line between paragraphs. <code>## Heading</code>, <code>- bullet</code>,
          <code>&gt; quote</code>, <code>**bold**</code>, <code>*italic*</code>,
          <code>[text](/link)</code>, <code>![alt](/image.jpg)</code>.
        </div>
      </div>

      <div class="field">
        <label for="tags">Tags</label>
        <input type="text" id="tags" name="tags" value="{{ old('tags', $tagList) }}"
               placeholder="counterfeits, anua, serums">
        <div class="hint">Comma separated. Readers can filter the blog by these.</div>
      </div>

    </div>
  </div>

  <div class="panel">
    <div class="panel-head"><h2>Search appearance</h2></div>
    <div class="panel-body">

      <div class="serp">
        <div class="serp-lab">Google preview</div>
        <div class="serp-url" id="serpUrl" data-root="{{ rtrim(url('/'), '/') }}">{{ rtrim(url('/'), '/') }}/blog/{{ $post->slug ?: 'post-address' }}</div>
        <div class="serp-title" id="serpTitle">{{ $post->meta_title ?: ($post->title ?: 'Post title') }}</div>
        <div class="serp-desc" id="serpDesc">{{ $post->meta_description ?: 'Write a meta description so Google shows your words.' }}</div>
      </div>

      <div class="field">
        <label for="focus_keyword">Focus keyword</label>
        <input type="text" id="focus_keyword" name="focus_keyword" value="{{ old('focus_keyword', $post->focus_keyword) }}"
               placeholder="fake anua serum">
        <div class="hint">The phrase you want this post to rank for. Everything on the right is measured against it.</div>
      </div>

      <div class="field">
        <label for="meta_title">Meta title <span class="counter" id="mtCount"></span></label>
        <input type="text" id="meta_title" name="meta_title" maxlength="70"
               value="{{ old('meta_title', $post->meta_title) }}" placeholder="{{ $post->title ?: 'Falls back to the post title' }}">
      </div>

      <div class="field">
        <label for="meta_description">Meta description <span class="counter" id="mdCount"></span></label>
        <textarea id="meta_description" name="meta_description" maxlength="200" style="min-height:66px">{{ old('meta_description', $post->meta_description) }}</textarea>
      </div>

      <div class="divider"><span>Sharing on Facebook and WhatsApp</span></div>

      <div class="field">
        <label for="og_title">Share title</label>
        <input type="text" id="og_title" name="og_title" maxlength="95" value="{{ old('og_title', $post->og_title) }}">
      </div>
      <div class="field">
        <label for="og_description">Share description</label>
        <textarea id="og_description" name="og_description" maxlength="200" style="min-height:60px">{{ old('og_description', $post->og_description) }}</textarea>
      </div>

      <div class="divider"><span>Advanced</span></div>

      <div class="field">
        <label for="canonical_url">Canonical URL</label>
        <input type="text" id="canonical_url" name="canonical_url" value="{{ old('canonical_url', $post->canonical_url) }}"
               placeholder="Only if this post appears elsewhere too">
      </div>
      <div class="field" style="margin:0">
        <label class="check">
          <input type="checkbox" name="is_indexable" value="1" @checked(old('is_indexable', $post->is_indexable ?? true))>
          Let search engines index this post
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
        <label for="status">Status</label>
        <select id="status" name="status">
          @foreach (\App\Models\Post::STATUSES as $key => $label)
            <option value="{{ $key }}" @selected(old('status', $post->status) === $key)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="field">
        <label for="published_at">Publish date</label>
        <input type="datetime-local" id="published_at" name="published_at"
               value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}"
               style="width:100%;border:1.5px solid var(--line);border-radius:3px;padding:10px 12px;font:inherit;font-size:14.5px;background:#fff">
        <div class="hint">A future date schedules it — it goes live on its own.</div>
      </div>
      <div class="field">
        <label class="check">
          <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $post->is_featured))>
          Headline this on the blog
        </label>
      </div>
      <button type="submit" class="btn btn-gold" style="width:100%">{{ $submit }}</button>
      <a href="{{ route('admin.posts.index') }}" class="btn btn-line" style="width:100%;margin-top:9px">Cancel</a>
      @if ($post->exists)
        <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="btn btn-line" style="width:100%;margin-top:9px">
          {{ $post->is_live ? 'View on site' : 'Preview draft' }}
        </a>
      @endif
    </div>
  </div>

  <div class="panel">
    <div class="panel-head"><h2>Cover image</h2></div>
    <div class="panel-body">
      @if ($post->cover_url)
        <img src="{{ $post->cover_url }}" alt="" class="cover-preview">
      @endif
      <div class="field">
        <input type="file" name="cover" accept="image/jpeg,image/png,image/webp">
        <div class="hint">Wide images work best. Resized to 1400px automatically.</div>
      </div>
      <div class="field" style="margin:0">
        <label for="cover_alt">Alt text</label>
        <input type="text" id="cover_alt" name="cover_alt" value="{{ old('cover_alt', $post->cover_alt) }}"
               placeholder="What the picture shows">
        <div class="hint">Read aloud to blind readers, and read by Google.</div>
      </div>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head"><h2>SEO analysis</h2></div>
    <div class="panel-body">
      <div class="seo-head" style="margin-bottom:14px">
        <div class="seo-score ok" id="seoScore">0</div>
        <div>
          <b id="seoScoreLabel">Needs work</b>
          <small>Updates as you type</small>
        </div>
      </div>

      <div class="seo-stats">
        <span><b id="wordCount">0</b> words</span>
        <span><b id="readMinutes">1</b> min read</span>
      </div>

      <div class="seo-tabs">
        <button type="button" class="on" data-seotab="seoChecks">SEO</button>
        <button type="button" data-seotab="readChecks">Readability</button>
      </div>

      <ul class="chklist" id="seoChecks"></ul>
      <ul class="chklist" id="readChecks" style="display:none"></ul>
    </div>
  </div>

</div>
</div>
