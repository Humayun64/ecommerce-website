<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>@yield('title', $settings['meta_title'] ?? 'AMJR Global')</title>
<meta name="description" content="@yield('meta_description', $settings['meta_description'] ?? '')">

@hasSection('og_title')
  <meta property="og:title" content="@yield('og_title')">
  <meta property="og:description" content="@yield('og_description')">
  <meta property="og:type" content="product">
@else
  <meta property="og:title" content="{{ $settings['meta_title'] ?? 'AMJR Global' }}">
  <meta property="og:description" content="{{ $settings['meta_description'] ?? '' }}">
  <meta property="og:type" content="website">
@endif
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:site_name" content="{{ $settings['store_name'] ?? 'AMJR Global' }}">
@yield('og_image')

@if (!empty($settings['favicon']))
  <link rel="icon" href="{{ Storage::url($settings['favicon']) }}">
@endif

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@75..125,400..800&family=Public+Sans:wght@400;500;600;700&family=Hind+Siliguri:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/site.css') }}">

@stack('head')
</head>
<body>

@include('site.partials.header')

@if (session('status'))
  <div class="wrap"><div class="flash">{{ session('status') }}</div></div>
@endif

@yield('content')

@include('site.partials.footer')

@stack('scripts')
</body>
</html>
