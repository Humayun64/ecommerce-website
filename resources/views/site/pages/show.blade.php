@extends('site.layouts.app')

@section('title', $page->seo_title . ' — ' . ($settings['store_name'] ?? 'AMJR Global'))
@section('meta_description', $page->seo_description)

@push('head')
  @unless ($page->is_indexable)<meta name="robots" content="noindex">@endunless
  <style>
    .cms{padding:34px 18px 60px;max-width:860px}
    .cms-head{margin-bottom:26px;padding-bottom:22px;border-bottom:1px solid var(--line)}
    .cms-head h1{font-family:var(--display);font-variation-settings:"wdth" 108;font-weight:700;letter-spacing:-.018em;font-size:clamp(26px,4vw,38px);margin:0;line-height:1.12}
    .cms-head p{color:var(--ink-mute);font-size:16.5px;margin:12px 0 0;line-height:1.6;max-width:60ch}
    .cms-body{background:#fff;border:1px solid var(--line);border-radius:6px;padding:32px 36px;box-shadow:0 1px 2px rgba(12,28,54,.05)}
    @media(max-width:600px){.cms-body{padding:24px 20px}}
    .cms-body p{font-size:16px;line-height:1.75;margin:0 0 1.15em;max-width:66ch;color:var(--ink)}
    .cms-body p:last-child{margin-bottom:0}
    .cms-body h3{font-family:var(--display);font-variation-settings:"wdth" 108;font-weight:700;font-size:21px;margin:1.9em 0 .7em;letter-spacing:-.012em}
    .cms-body h3:first-child{margin-top:0}
    .cms-body h3::after{content:"";display:block;width:36px;height:3px;background:var(--gold);margin-top:10px}
    .cms-body h4{font-family:var(--display);font-variation-settings:"wdth" 106;font-weight:700;font-size:16.5px;margin:1.6em 0 .5em}
    .cms-body ul{margin:0 0 1.15em;padding-left:22px;max-width:66ch}
    .cms-body li{font-size:16px;line-height:1.7;margin-bottom:.45em}
    .cms-body a{color:var(--navy);text-decoration:underline;text-underline-offset:2px}
    .cms-body strong{font-weight:600}
    .cms-foot{margin-top:24px;display:flex;gap:10px;flex-wrap:wrap}
  </style>
@endpush

@section('content')

<div class="crumbs"><div class="wrap">
  <a href="{{ route('home') }}">{{ __('Home') }}</a><span>/</span>
  <strong>{{ $page->title }}</strong>
</div></div>

<div class="wrap cms">
  <div class="cms-head">
    <h1>{{ $page->title }}</h1>
    @if ($page->excerpt)<p>{{ $page->excerpt }}</p>@endif
  </div>

  <article class="cms-body">
    {!! \App\Support\Markup::render($page->content) !!}
  </article>

  <div class="cms-foot">
    <a href="{{ route('shop') }}" class="btn btn-gold">{{ __('Browse products') }}</a>
    @if (!empty($settings['store_whatsapp']))
      <a href="https://wa.me/{{ $settings['store_whatsapp'] }}" target="_blank" rel="noopener" class="btn btn-line">{{ __('Ask us on WhatsApp') }}</a>
    @endif
  </div>
</div>

@endsection
