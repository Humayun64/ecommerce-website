@if (($latestPosts ?? collect())->isNotEmpty())
  <style>.bl-strip{background:linear-gradient(180deg,#F4F5F7,#EAEEF5);border-top:1px solid var(--line);padding:38px 0 40px}
.bl-strip .wrap{display:grid;grid-template-columns:230px minmax(0,1fr);gap:30px;align-items:center}
@media(max-width:820px){.bl-strip .wrap{grid-template-columns:1fr;gap:18px}}
.bl-strip h4{font-family:var(--display);font-variation-settings:"wdth" 110;font-weight:700;font-size:19px;color:var(--navy);margin:0 0 8px}
.bl-strip h4::after{content:"";display:block;width:38px;height:3px;border-radius:3px;background:var(--gold);margin-top:10px}
.bl-strip .lead{font-size:13.5px;color:var(--ink-mute);line-height:1.6;margin:12px 0 14px}
.bl-strip .all{display:inline-flex;align-items:center;gap:6px;font-size:13.5px;color:var(--navy);font-weight:600;border-bottom:2px solid var(--gold);padding-bottom:2px}
.bl-strip .all::after{content:"\2192";transition:transform .2s}
.bl-strip .all:hover::after{transform:translateX(4px)}
.bl-striplist{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
@media(max-width:700px){.bl-striplist{grid-template-columns:1fr}}
.bl-stripitem{position:relative;display:block;background:#fff;border:1px solid var(--line);border-radius:12px;padding:18px 18px 16px;overflow:hidden;transition:transform .18s,box-shadow .18s,border-color .18s}
.bl-stripitem::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;background:linear-gradient(180deg,var(--gold),var(--navy))}
.bl-stripitem:hover{transform:translateY(-3px);border-color:var(--navy);box-shadow:0 18px 30px -22px rgba(12,28,54,.5)}
.bl-stripitem b{display:block;font-size:14.5px;font-weight:600;color:var(--ink);line-height:1.4;margin-bottom:8px}
.bl-stripitem small{font-size:12px;color:var(--ink-mute)}</style>
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
