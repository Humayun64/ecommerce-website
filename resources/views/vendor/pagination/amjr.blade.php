@if ($paginator->hasPages())
  <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap">
    <div class="sub">
      Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
    </div>
    <div style="display:flex;gap:6px">
      @if ($paginator->onFirstPage())
        <span class="btn btn-line btn-sm" style="opacity:.45">Previous</span>
      @else
        <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-line btn-sm">Previous</a>
      @endif

      @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-line btn-sm">Next</a>
      @else
        <span class="btn btn-line btn-sm" style="opacity:.45">Next</span>
      @endif
    </div>
  </div>
@endif
