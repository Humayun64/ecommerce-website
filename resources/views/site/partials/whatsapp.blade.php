{{-- Floating chat button. Switched on and given a number in Admin > WhatsApp. --}}
@php
  $waChat = app(\App\Services\WhatsApp::class);
  // On a product page the message already names the product.
  $waText = isset($product) && $product instanceof \App\Models\Product && request()->routeIs('shop.product')
      ? __('Hello, I want to know about this product:') . ' ' . $product->name . ' ' . route('shop.product', $product)
      : null;
  $waUrl = $waChat->chatUrl($waText);
@endphp
@if ($waUrl)
  <a href="{{ $waUrl }}" class="wa-fab" target="_blank" rel="noopener noreferrer" aria-label="{{ __('Chat with us on WhatsApp') }}">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3a9 9 0 0 0-7.7 13.6L3 21l4.5-1.2A9 9 0 1 0 12 3z"/><circle cx="8.4" cy="12" r="1.1"/><circle cx="12" cy="12" r="1.1"/><circle cx="15.6" cy="12" r="1.1"/></svg>
    <span>{{ __('Chat on WhatsApp') }}</span>
  </a>
@endif
