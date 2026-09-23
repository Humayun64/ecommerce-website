@extends('site.layouts.app')

@section('title', __('Blog') . ' — ' . ($settings['store_name'] ?? 'AMJR Global'))
@section('meta_description', __('Skincare advice, product guides and how to tell genuine Korean products from fakes.'))

@push('head')<link rel="stylesheet" href="{{ asset('css/blog.css') }}">@endpush

@section('content')
<div class="bl">

  <div class="bl-hero"><div class="wrap">
    <h1>{{ __('The blog') }}</h1>
    <p>{{ __('What works, what does not, and how to tell a genuine bottle from a convincing fake.') }}</p>
  </div></div>

  <div class="wrap bl-wrap">

    <div class="bl-filters">
      <form method="GET" class="bl-search">
        @if ($activeCategory)<input type="hidden" name="category" value="{{ $activeCategory }}">@endif
        <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('Search the blog') }}" aria-label="{{ __('Search the blog') }}">
        <button type="submit" aria-label="{{ __('Search') }}">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
        </button>
      </form>

      <div class="bl-chips">
        <a href="{{ route('blog.index') }}" class="bl-chip {{ ! $activeCategory && ! $activeTag ? 'on' : '' }}">{{ __('Everything') }}</a>
        @foreach ($categories as $category)
          <a href="{{ route('blog.index', ['category' => $category->slug]) }}"
             class="bl-chip {{ $activeCategory === $category->slug ? 'on' : '' }}">
            {{ $category->name }} <small>{{ $category->posts_count }}</small>
          </a>
        @endforeach
      </div>
    </div>

    @if ($activeTag)
      <p class="sub" style="margin:-8px 0 18px">
        {{ __('Tagged') }} <strong>{{ $activeTag }}</strong> —
        <a href="{{ route('blog.index') }}" style="text-decoration:underline">{{ __('clear') }}</a>
      </p>
    @endif

    @if ($featured)
      <article class="bl-featured">
        <div class="shot">
          @if ($featured->cover_url)
            <img src="{{ $featured->cover_url }}" alt="{{ $featured->cover_alt }}">
          @endif
        </div>
        <div class="body">
          <span class="bl-tagline">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.6l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.4 6.2 20.5l1.1-6.5L2.6 9.4l6.5-.9z"/></svg>
            {{ $featured->category?->name ?? __('Featured') }}
          </span>
          <h2><a href="{{ $featured->url }}">{{ $featured->title }}</a></h2>
          <p>{{ $featured->summary }}</p>
          <div class="bl-meta" style="border-top:0;padding-top:0">
            @if ($featured->published_at){{ $featured->published_at->format('j M Y') }}<span class="sep">·</span>@endif
            {{ __(':n min read', ['n' => $featured->reading_minutes]) }}
          </div>
        </div>
      </article>
    @endif

    @if ($posts->isEmpty())
      <div class="bl-none">
        <b>{{ __('Nothing here yet') }}</b>
        <p>{{ request('q') ? __('No post matched that search.') : __('The first post is on its way.') }}</p>
        <a href="{{ route('shop') }}" class="btn btn-gold">{{ __('Browse the shop') }}</a>
      </div>
    @else
      <div class="bl-grid">
        @foreach ($posts as $post)
          <article class="bl-card">
            <a href="{{ $post->url }}" class="shot">
              @if ($post->cover_url)
                <img src="{{ $post->cover_url }}" alt="{{ $post->cover_alt }}" loading="lazy">
              @else
                <svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="#14305A" stroke-opacity=".22" stroke-width="1.6"><path d="M14 2.5H6.5a1 1 0 00-1 1v17a1 1 0 001 1h11a1 1 0 001-1V7z"/><path d="M14 2.5V7h4.5M8.5 12h7M8.5 16h7"/></svg>
              @endif
            </a>
            <div class="body">
              @if ($post->category)
                <span class="bl-tagline">{{ $post->category->name }}</span>
              @endif
              <h3><a href="{{ $post->url }}">{{ $post->title }}</a></h3>
              <p>{{ Str::limit($post->summary, 110) }}</p>
              <div class="bl-meta">
                @if ($post->published_at){{ $post->published_at->format('j M Y') }}<span class="sep">·</span>@endif
                {{ __(':n min read', ['n' => $post->reading_minutes]) }}
              </div>
            </div>
          </article>
        @endforeach
      </div>

      <div class="pagination">{{ $posts->onEachSide(1)->links('vendor.pagination.site') }}</div>
    @endif

    @if ($tags->isNotEmpty())
      <div style="margin-top:34px">
        <h4 style="font-size:13px;color:var(--ink-mute);margin:0 0 10px">{{ __('Browse by tag') }}</h4>
        <div class="bl-chips">
          @foreach ($tags as $tag)
            <a href="{{ route('blog.index', ['tag' => $tag->slug]) }}"
               class="bl-chip {{ $activeTag === $tag->slug ? 'on' : '' }}">{{ $tag->name }}</a>
          @endforeach
        </div>
      </div>
    @endif

  </div>
</div>
@endsection
