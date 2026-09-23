{{-- $rating float, optional $cls --}}
<span class="pp-stars {{ $cls ?? '' }}" aria-label="{{ __(':n out of 5', ['n' => round($rating, 1)]) }}">
  @for ($i = 1; $i <= 5; $i++)
    <svg viewBox="0 0 24 24" aria-hidden="true" class="{{ $i <= round($rating) ? 'full' : 'empty' }}"><path d="M12 2.6l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.4 6.2 20.5l1.1-6.5L2.6 9.4l6.5-.9z"/></svg>
  @endfor
</span>
