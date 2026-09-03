{{-- Quick-add used on product cards for single-size products. --}}
<form method="POST" action="{{ route('cart.store') }}" class="quickadd">
  @csrf
  <input type="hidden" name="product_id" value="{{ $product->id }}">
  <input type="hidden" name="quantity" value="1">
  <button type="submit" class="add" @disabled($product->is_out_of_stock)>
    {{ $product->is_out_of_stock ? __('Sold out') : __('Add to cart') }}
  </button>
</form>
