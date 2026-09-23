@if (($latestPosts ?? collect())->isNotEmpty())
  <style>.bl-strip{background:var(--navy-deep);border-top:1px solid rgba(255,255,255,.08);padding:34px 0 10px}
.bl-strip .wrap{display:grid;grid-template-columns:200px minmax(0,1fr);gap:30px;align-items:start}
@media(max-width:820px){.bl-strip .wrap{grid-template-columns:1fr;gap:18px}}
.bl-strip h4{font-family:var(--display);font-variation-settings:"wdth" 108;font-weight:700;font-size:15px;color:#fff;margin:0 0 8px}
.bl-strip .lead{font-size:13px;color:#8E9BB0;line-height:1.55;margin:0 0 12px}
.bl-strip .all{font-size:13.5px;color:var(--gold);font-weight:600}
.bl-striplist{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
@media(max-width:700px){.bl-striplist{grid-template-columns:1fr}}
.bl-stripitem{display:block;padding-left:14px;border-left:2px solid rgba(255,255,255,.12)}
.bl-stripitem:hover{border-left-color:var(--gold)}
.bl-stripitem b{display:block;font-size:14px;font-weight:600;color:#DDE4EF;line-height:1.4;margin-bottom:5px}
.bl-stripitem small{font-size:12px;color:#8E9BB0}</style>
  <div class="bl-strip">
    <div class="wrap">
      <div>
        <h4>{{ __('From the blog') }}</h4>
        <p class="lead">{{ __('Advice on spotting fakes, building a routine and getting the most from what you buy.') }}</p>
        <a href="{{ route('blog.index') }}" class="all">{{ __('Read all posts') }}</a>
      </div>
      <div class="bl-striplist">
        @foreach ($latestPosts as $post)
          <a href="{{ $post->url }}" class="bl-stripitem">
            <b>{{ Str::limit($post->title, 62) }}</b>
            <small>
              @if ($post->published_at){{ $post->published_at->format('j M Y') }} · @endif
              {{ __(':n min read', ['n' => $post->reading_minutes]) }}
            </small>
          </a>
        @endforeach
      </div>
    </div>
  </div>
@endif
