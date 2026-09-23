@extends('site.layouts.app')

@section('title', $post->seo_title . ' — ' . ($settings['store_name'] ?? 'AMJR Global'))
@section('meta_description', $post->seo_description)
@section('og_title', $post->og_title ?: $post->seo_title)
@section('og_description', $post->og_description ?: $post->seo_description)
@section('og_image')
  @if ($post->cover_url)<meta property="og:image" content="{{ url($post->cover_url) }}">@endif
@endsection

@push('head')
  <link rel="stylesheet" href="{{ asset('css/blog.css') }}">
  @unless ($post->is_indexable && $post->is_live)<meta name="robots" content="noindex">@endunless
  @if ($post->canonical_url)<link rel="canonical" href="{{ $post->canonical_url }}">@endif
  @php
    $articleSchema = [
        '@context'      => 'https://schema.org',
        '@type'         => 'BlogPosting',
        'headline'      => $post->title,
        'description'   => $post->seo_description,
        'datePublished' => $post->published_at?->toIso8601String(),
        'dateModified'  => $post->updated_at?->toIso8601String(),
        'author'        => ['@type' => 'Person', 'name' => $post->author?->name ?? ($settings['store_name'] ?? 'AMJR Global')],
        'publisher'     => ['@type' => 'Organization', 'name' => $settings['store_name'] ?? 'AMJR Global'],
        'mainEntityOfPage' => $post->url,
    ];
    if ($post->cover_url) { $articleSchema['image'] = url($post->cover_url); }
  @endphp
  <script type="application/ld+json">{!! json_encode($articleSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
<div class="bl">

  <div class="crumbs"><div class="wrap">
    <a href="{{ route('home') }}">{{ __('Home') }}</a><span>/</span>
    <a href="{{ route('blog.index') }}">{{ __('Blog') }}</a><span>/</span>
    <strong>{{ Str::limit($post->title, 60) }}</strong>
  </div></div>

  <div class="bl-post">

    @unless ($post->is_live)
      <div class="bl-draft">
        {{ __('This is a preview. The post is :status and nobody else can see it.', ['status' => strtolower(\App\Models\Post::STATUSES[$post->status] ?? $post->status)]) }}
      </div>
    @endunless

    @if ($post->cover_url)
      <div class="bl-cover"><img src="{{ $post->cover_url }}" alt="{{ $post->cover_alt }}"></div>
    @endif

    <header class="bl-head">
      @if ($post->category)
        <a href="{{ route('blog.index', ['category' => $post->category->slug]) }}" class="bl-tagline">{{ $post->category->name }}</a>
      @endif
      <h1>{{ $post->title }}</h1>
      @if ($post->excerpt)<p class="bl-sum">{{ $post->excerpt }}</p>@endif

      <div class="bl-byline">
        <span class="bl-av">{{ strtoupper(Str::substr($post->author?->name ?? 'A', 0, 2)) }}</span>
        <span>{{ $post->author?->name ?? ($settings['store_name'] ?? 'AMJR Global') }}</span>
        @if ($post->published_at)<span class="sep">·</span><span>{{ $post->published_at->format('j M Y') }}</span>@endif
        <span class="sep">·</span><span>{{ __(':n min read', ['n' => $post->reading_minutes]) }}</span>
      </div>
    </header>

    <div class="bl-layout">
      @php $headings = $post->headings; @endphp

      <aside>
        @if (count($headings) > 2)
          <nav class="bl-toc">
            <h4>{{ __('In this post') }}</h4>
            <ol>
              @foreach ($headings as $heading)
                <li><a href="#{{ $heading['anchor'] }}">{{ $heading['text'] }}</a></li>
              @endforeach
            </ol>
          </nav>
        @endif
      </aside>

      <div>
        <article class="bl-body">
          {!! $post->body_html !!}

          @if ($post->tags->isNotEmpty())
            <div class="bl-tags">
              @foreach ($post->tags as $tag)
                <a href="{{ route('blog.index', ['tag' => $tag->slug]) }}" class="bl-tag">{{ $tag->name }}</a>
              @endforeach
            </div>
          @endif
        </article>

        @php $share = urlencode($post->url); $shareTitle = urlencode($post->title); @endphp
        <div class="bl-share">
          <span>{{ __('Share') }}</span>
          <a href="https://www.facebook.com/sharer/sharer.php?u={{ $share }}" target="_blank" rel="noopener" aria-label="Facebook">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 22v-8h2.7l.4-3.1h-3.1V8.9c0-.9.25-1.5 1.55-1.5h1.65V4.63A22 22 0 0014.3 4.5c-2.4 0-4 1.45-4 4.12V10.9H7.6V14h2.7v8z"/></svg>
          </a>
          <a href="https://wa.me/?text={{ $shareTitle }}%20{{ $share }}" target="_blank" rel="noopener" aria-label="WhatsApp">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 00-8.6 15L2 22l5.2-1.4A10 10 0 1012 2zm0 18.2a8.2 8.2 0 01-4.2-1.2l-.3-.2-3.1.8.8-3-.2-.3A8.2 8.2 0 1112 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.6.8-.8 1-.3.2-.5.1a6.7 6.7 0 01-3.3-2.9c-.2-.4.2-.4.6-1.2.1-.2 0-.3 0-.5s-.6-1.4-.8-2c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 00-.7.3A3 3 0 006 8.6a5.2 5.2 0 001.1 2.7 11.9 11.9 0 004.6 4 5.3 5.3 0 002.4.5 2.8 2.8 0 001.9-1.3 2.3 2.3 0 00.2-1.3c-.1-.1-.3-.2-.5-.3z"/></svg>
          </a>
          <a href="mailto:?subject={{ $shareTitle }}&body={{ $share }}" aria-label="Email">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><rect x="2.5" y="5" width="19" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
          </a>
        </div>
      </div>
    </div>

    @if ($related->isNotEmpty())
      <div class="bl-related">
        <h2>{{ __('Read next') }}</h2>
        <p class="sub">{{ __('More from the blog.') }}</p>
        <div class="bl-grid">
          @foreach ($related as $item)
            <article class="bl-card">
              <a href="{{ $item->url }}" class="shot">
                @if ($item->cover_url)
                  <img src="{{ $item->cover_url }}" alt="{{ $item->cover_alt }}" loading="lazy">
                @endif
              </a>
              <div class="body">
                <h3><a href="{{ $item->url }}">{{ $item->title }}</a></h3>
                <p>{{ Str::limit($item->summary, 90) }}</p>
                <div class="bl-meta">{{ __(':n min read', ['n' => $item->reading_minutes]) }}</div>
              </div>
            </article>
          @endforeach
        </div>
      </div>
    @endif

  </div>
</div>
@endsection
