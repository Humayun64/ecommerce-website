@if ($paginator->hasPages())
  <nav class="pager-nav" aria-label="{{ __('Pagination') }}">
    @if ($paginator->onFirstPage())
      <span class="pg disabled">{{ __('Previous') }}</span>
    @else
      <a href="{{ $paginator->previousPageUrl() }}" class="pg" rel="prev">{{ __('Previous') }}</a>
    @endif

    @foreach ($elements as $element)
      @if (is_string($element))
        <span class="pg gap">{{ $element }}</span>
      @endif
      @if (is_array($element))
        @foreach ($element as $page => $url)
          @if ($page == $paginator->currentPage())
            <span class="pg on">{{ $page }}</span>
          @else
            <a href="{{ $url }}" class="pg">{{ $page }}</a>
          @endif
        @endforeach
      @endif
    @endforeach

    @if ($paginator->hasMorePages())
      <a href="{{ $paginator->nextPageUrl() }}" class="pg" rel="next">{{ __('Next') }}</a>
    @else
      <span class="pg disabled">{{ __('Next') }}</span>
    @endif
  </nav>
@endif
