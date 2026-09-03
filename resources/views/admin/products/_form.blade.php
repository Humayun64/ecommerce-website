@csrf
@if ($method === 'PUT') @method('PUT') @endif

@if ($errors->any())
  <div class="alert alert-bad">
    <strong>{{ $errors->count() }} problem{{ $errors->count() > 1 ? 's' : '' }} to fix:</strong>
    <ul style="margin:8px 0 0;padding-left:18px">
      @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
  </div>
@endif

<div class="form-grid">
<div class="form-main">

  {{-- ---------- basics ---------- --}}
  <div class="panel">
    <div class="panel-head"><h2>Product details</h2></div>
    <div class="panel-body">

      <div class="field">
        <label for="name">Product name</label>
        <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required
               placeholder="Anua Niacinamide 10% + TXA 4% Serum">
        <div class="hint">Lead with the brand. Customers search by brand first.</div>
      </div>

      <div class="row2">
        <div class="field">
          <label for="sku">SKU</label>
          <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required placeholder="ANU-NIA-01">
          <div class="hint">Your internal code. Must be unique.</div>
        </div>
        <div class="field">
          <label for="slug">URL slug</label>
          <input type="text" id="slug" name="slug" value="{{ old('slug', $product->slug) }}" placeholder="Leave blank to generate">
          <div class="hint">Changing this on a live product breaks existing links.</div>
        </div>
      </div>

      <div class="row2">
        <div class="field">
          <label for="brand_id">Brand</label>
          <select id="brand_id" name="brand_id">
            <option value="">No brand</option>
            @foreach ($brands as $brand)
              <option value="{{ $brand->id }}" @selected(old('brand_id', $product->brand_id) == $brand->id)>{{ $brand->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="field">
          <label for="category_id">Category</label>
          <select id="category_id" name="category_id">
            <option value="">Uncategorised</option>
            @foreach ($categories as $category)
              <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->full_name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="row2">
        <div class="field">
          <label for="origin">Country of origin</label>
          <input type="text" id="origin" name="origin" value="{{ old('origin', $product->origin) }}" placeholder="South Korea">
          <div class="hint">Shown as the tag on product cards.</div>
        </div>
        <div class="field">
          <label for="size_label">Size label</label>
          <input type="text" id="size_label" name="size_label" value="{{ old('size_label', $product->size_label) }}" placeholder="30 ml">
        </div>
      </div>

      <div class="field">
        <label for="short_description">Short description</label>
        <textarea id="short_description" name="short_description" style="min-height:64px"
                  placeholder="One line shown under the product name in listings.">{{ old('short_description', $product->short_description) }}</textarea>
      </div>

      <div class="field">
        <label for="description">Full description</label>
        <textarea id="description" name="description" style="min-height:170px">{{ old('description', $product->description) }}</textarea>
        <div class="hint">What it does, how to use it, who it suits. This is what Google reads.</div>
      </div>

    </div>
  </div>

  {{-- ---------- pricing ---------- --}}
  <div class="panel">
    <div class="panel-head">
      <h2>Pricing and stock</h2>
      <span class="sub" id="marginReadout"></span>
    </div>
    <div class="panel-body">

      <div class="row3">
        <div class="field">
          <label for="price">Selling price (৳)</label>
          <input type="number" step="0.01" min="0" id="price" name="price" value="{{ old('price', $product->price) }}" required>
        </div>
        <div class="field">
          <label for="compare_price">Old price (৳)</label>
          <input type="number" step="0.01" min="0" id="compare_price" name="compare_price" value="{{ old('compare_price', $product->compare_price) }}">
          <div class="hint">Shown struck through. Leave empty if not on offer.</div>
        </div>
        <div class="field">
          <label for="cost_price">Your cost (৳)</label>
          <input type="number" step="0.01" min="0" id="cost_price" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}">
          <div class="hint">Never shown to customers. Drives profit reporting.</div>
        </div>
      </div>

      <div class="row2" id="simpleStock">
        <div class="field">
          <label for="stock">Stock quantity</label>
          <input type="number" min="0" id="stock" name="stock" value="{{ old('stock', $product->stock) }}">
        </div>
        <div class="field">
          <label for="low_stock_threshold">Warn me at</label>
          <input type="number" min="0" id="low_stock_threshold" name="low_stock_threshold"
                 value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}">
          <div class="hint">Flags on the dashboard at or below this number.</div>
        </div>
      </div>

      <div class="note" id="variantStockNote" style="display:none">
        This product has sizes, so stock is tracked per size below. The quantity above is ignored.
      </div>

    </div>
  </div>

  {{-- ---------- variations ---------- --}}
  <div class="panel">
    <div class="panel-head">
      <h2>Variations</h2>
      <a href="{{ route('admin.attributes.index') }}" class="btn btn-line btn-sm" target="_blank">Manage attributes</a>
    </div>
    <div class="panel-body">

      @if ($allAttributes->isEmpty())
        <div class="empty" style="padding:26px">
          <b>No attributes defined yet</b>
          Create one — Size, with your millilitre values — then come back here.
        </div>
      @else

        <p class="sub" style="margin:0 0 14px">
          Tick the axes this product varies on, choose which values it comes in, then generate
          the grid. Leave everything unticked for a single-size product.
        </p>

        @php
          $chosenAxes = old('attribute_ids', $product->productAttributes->pluck('id')->all());
        @endphp

        <div class="axes">
          @foreach ($allAttributes as $attribute)
            @php $isOn = in_array($attribute->id, $chosenAxes); @endphp
            <div class="axis {{ $isOn ? 'on' : '' }}" data-axis="{{ $attribute->id }}">
              <label class="check axis-head">
                <input type="checkbox" name="attribute_ids[]" value="{{ $attribute->id }}"
                       class="axisToggle" @checked($isOn)>
                <strong>{{ $attribute->name }}</strong>
              </label>

              <div class="axis-values">
                @forelse ($attribute->values as $value)
                  <label class="vchip">
                    <input type="checkbox" class="valueToggle"
                           value="{{ $value->id }}"
                           data-label="{{ $value->value }}"
                           data-axis="{{ $attribute->id }}">
                    <span>{{ $value->value }}</span>
                  </label>
                @empty
                  <span class="sub">No values on this attribute yet.</span>
                @endforelse
              </div>
            </div>
          @endforeach
        </div>

        <div class="matrix-bar">
          <button type="button" class="btn btn-navy btn-sm" id="generateMatrix">Generate variations</button>
          <span class="sub" id="matrixHint">Pick values above, then generate.</span>
        </div>

        <div class="vtable-wrap" style="margin-top:16px">
          <table class="vtable">
            <thead>
              <tr>
                <th style="min-width:150px">Variation</th>
                <th style="min-width:110px">SKU</th>
                <th style="min-width:95px">Price</th>
                <th style="min-width:95px">Old price</th>
                <th style="min-width:95px">Cost</th>
                <th style="min-width:80px">Stock</th>
                <th></th>
              </tr>
            </thead>
            <tbody id="variantRows">
              @php $existing = old('variants', []); @endphp
              @if ($existing)
                @foreach ($existing as $i => $variant)
                  <tr data-key="{{ collect($variant['value_ids'] ?? [])->sort()->implode('-') }}">
                    <td>
                      <input type="hidden" name="variants[{{ $i }}][id]" value="{{ $variant['id'] ?? '' }}">
                      <input type="hidden" name="variants[{{ $i }}][name]" value="{{ $variant['name'] ?? '' }}">
                      @foreach ($variant['value_ids'] ?? [] as $vid)
                        <input type="hidden" name="variants[{{ $i }}][value_ids][]" value="{{ $vid }}">
                      @endforeach
                      <span class="vlabel">{{ $variant['name'] ?? '' }}</span>
                    </td>
                    <td><input type="text" name="variants[{{ $i }}][sku]" value="{{ $variant['sku'] ?? '' }}" placeholder="auto"></td>
                    <td><input type="number" step="0.01" min="0" name="variants[{{ $i }}][price]" value="{{ $variant['price'] ?? '' }}"></td>
                    <td><input type="number" step="0.01" min="0" name="variants[{{ $i }}][compare_price]" value="{{ $variant['compare_price'] ?? '' }}"></td>
                    <td><input type="number" step="0.01" min="0" name="variants[{{ $i }}][cost_price]" value="{{ $variant['cost_price'] ?? '' }}"></td>
                    <td><input type="number" min="0" name="variants[{{ $i }}][stock]" value="{{ $variant['stock'] ?? 0 }}"></td>
                    <td><button type="button" class="btn btn-danger btn-sm removeVariant">Remove</button></td>
                  </tr>
                @endforeach
              @else
                @foreach ($product->variants as $i => $variant)
                  <tr data-key="{{ $variant->values->pluck('id')->sort()->implode('-') }}">
                    <td>
                      <input type="hidden" name="variants[{{ $i }}][id]" value="{{ $variant->id }}">
                      <input type="hidden" name="variants[{{ $i }}][name]" value="{{ $variant->name }}">
                      @foreach ($variant->values as $value)
                        <input type="hidden" name="variants[{{ $i }}][value_ids][]" value="{{ $value->id }}">
                      @endforeach
                      <span class="vlabel">{{ $variant->name }}</span>
                    </td>
                    <td><input type="text" name="variants[{{ $i }}][sku]" value="{{ $variant->sku }}" placeholder="auto"></td>
                    <td><input type="number" step="0.01" min="0" name="variants[{{ $i }}][price]" value="{{ $variant->price }}"></td>
                    <td><input type="number" step="0.01" min="0" name="variants[{{ $i }}][compare_price]" value="{{ $variant->compare_price }}"></td>
                    <td><input type="number" step="0.01" min="0" name="variants[{{ $i }}][cost_price]" value="{{ $variant->cost_price }}"></td>
                    <td><input type="number" min="0" name="variants[{{ $i }}][stock]" value="{{ $variant->stock }}"></td>
                    <td><button type="button" class="btn btn-danger btn-sm removeVariant">Remove</button></td>
                  </tr>
                @endforeach
              @endif
            </tbody>
          </table>
        </div>

        <div class="empty" id="noVariants" style="display:none;padding:22px">Single variation product.</div>

      @endif
    </div>
  </div>

  {{-- ---------- images ---------- --}}
  <div class="panel">
    <div class="panel-head"><h2>Images</h2></div>
    <div class="panel-body">

      @if ($product->exists && $product->images->count())
        <div class="imgrid">
          @foreach ($product->images as $image)
            <div class="imgcell {{ $image->is_primary ? 'main' : '' }}">
              <img src="{{ $image->url }}" alt="{{ $image->alt }}">
              @if ($image->is_primary)<span class="badge b-gold imgtag">Main</span>@endif
              <div class="imgacts">
                @if (! $image->is_primary)
                  <button type="button" class="btn btn-line btn-sm"
                          onclick="document.getElementById('primary-{{ $image->id }}').submit()">Make main</button>
                @endif
                <button type="button" class="btn btn-danger btn-sm"
                        onclick="if(confirm('Remove this image?')) document.getElementById('del-{{ $image->id }}').submit()">Remove</button>
              </div>
            </div>
          @endforeach
        </div>
      @endif

      <div class="field" style="margin-top:16px">
        <label for="images">Upload images</label>
        <input type="file" id="images" name="images[]" multiple accept="image/jpeg,image/png,image/webp">
        <div class="hint">
          Up to 8 at a time, 6 MB each. Square photos on a plain background work best.
          Large images are automatically resized down to 1400px, so upload straight from your phone.
        </div>
      </div>

    </div>
  </div>

  {{-- ---------- seo ---------- --}}
  <div class="panel">
    <div class="panel-head"><h2>Search and sharing</h2></div>
    <div class="panel-body">

      <div class="serp">
        <div class="serp-lab">Google preview</div>
        <div class="serp-url">amjrglobal.com › products › <span id="serpSlug">{{ $product->slug ?: 'product-name' }}</span></div>
        <div class="serp-title" id="serpTitle">{{ $product->meta_title ?: ($product->name ?: 'Product name') }}</div>
        <div class="serp-desc" id="serpDesc">{{ $product->meta_description ?: 'Write a meta description so Google shows your words instead of picking a random sentence from the page.' }}</div>
      </div>

      <div class="field">
        <label for="meta_title">Meta title <span class="counter" id="mtCount"></span></label>
        <input type="text" id="meta_title" name="meta_title" maxlength="70"
               value="{{ old('meta_title', $product->meta_title) }}"
               placeholder="{{ $product->name ?: 'Falls back to the product name' }}">
        <div class="hint">Aim for 50–60 characters. Put the brand and the key benefit in.</div>
      </div>

      <div class="field">
        <label for="meta_description">Meta description <span class="counter" id="mdCount"></span></label>
        <textarea id="meta_description" name="meta_description" maxlength="180" style="min-height:66px"
                  placeholder="One or two sentences that would make someone click.">{{ old('meta_description', $product->meta_description) }}</textarea>
        <div class="hint">Aim for 140–160 characters. This is your advert on the results page.</div>
      </div>

      <div class="divider"><span>Facebook and WhatsApp sharing</span></div>

      <p class="sub" style="margin:0 0 14px">
        These control the card people see when your product link is pasted into a Facebook post or a WhatsApp message.
        Leave them empty and the meta fields above are used instead.
      </p>

      <div class="field">
        <label for="og_title">Share title</label>
        <input type="text" id="og_title" name="og_title" maxlength="95" value="{{ old('og_title', $product->og_title) }}">
      </div>

      <div class="field">
        <label for="og_description">Share description</label>
        <textarea id="og_description" name="og_description" maxlength="200" style="min-height:60px">{{ old('og_description', $product->og_description) }}</textarea>
      </div>

      <div class="divider"><span>Advanced</span></div>

      <div class="field">
        <label for="canonical_url">Canonical URL</label>
        <input type="text" id="canonical_url" name="canonical_url" value="{{ old('canonical_url', $product->canonical_url) }}"
               placeholder="Leave empty unless this product duplicates another page">
        <div class="hint">Only set this if the same product exists at another URL you want Google to prefer.</div>
      </div>

      <div class="field">
        <label class="check">
          <input type="checkbox" name="is_indexable" value="1" @checked(old('is_indexable', $product->is_indexable ?? true))>
          Allow search engines to index this product
        </label>
        <div class="hint">Untick for test products or items you only sell through Facebook.</div>
      </div>

    </div>
  </div>

</div>

{{-- ---------- sidebar ---------- --}}
<div class="form-side">

  <div class="panel">
    <div class="panel-head"><h2>Publish</h2></div>
    <div class="panel-body">
      <div class="field">
        <label class="check">
          <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))>
          Visible on the storefront
        </label>
      </div>
      <div class="field" style="margin-bottom:8px">
        <label class="check">
          <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))>
          Feature on the homepage
        </label>
      </div>
      <div class="form-actions" style="flex-direction:column">
        <button type="submit" class="btn btn-gold" style="width:100%">{{ $submit }}</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-line" style="width:100%">Cancel</a>
      </div>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head"><h2>Type</h2></div>
    <div class="panel-body">
      <div class="field" style="margin:0">
        <select name="type" id="type">
          <option value="physical" @selected(old('type', $product->type) === 'physical')>Physical — needs shipping</option>
          <option value="digital" @selected(old('type', $product->type) === 'digital')>Digital — download</option>
        </select>
        <div class="hint">Everything you sell now is physical. Digital delivery is not built yet.</div>
      </div>
    </div>
  </div>

  @if ($product->exists)
    <div class="panel">
      <div class="panel-head"><h2>At a glance</h2></div>
      <div class="panel-body">
        <div class="glance"><span>Stock on hand</span><b>{{ $product->total_stock }}</b></div>
        <div class="glance"><span>Unit profit</span><b>{{ $product->unit_profit === null ? 'Set a cost' : '৳' . number_format($product->unit_profit) }}</b></div>
        <div class="glance"><span>Margin</span><b>{{ $product->margin_percent === null ? '—' : $product->margin_percent . '%' }}</b></div>
        <div class="glance"><span>Stock value</span><b>৳{{ number_format($product->stock_value) }}</b></div>
      </div>
    </div>
  @endif

</div>
</div>

<template id="variantTemplate">
  <tr data-key="__key__">
    <td>
      <input type="hidden" name="variants[__i__][id]" value="">
      <input type="hidden" name="variants[__i__][name]" value="__label__">
      __valueinputs__
      <span class="vlabel">__label__</span>
    </td>
    <td><input type="text" name="variants[__i__][sku]" placeholder="auto"></td>
    <td><input type="number" step="0.01" min="0" name="variants[__i__][price]"></td>
    <td><input type="number" step="0.01" min="0" name="variants[__i__][compare_price]"></td>
    <td><input type="number" step="0.01" min="0" name="variants[__i__][cost_price]"></td>
    <td><input type="number" min="0" name="variants[__i__][stock]" value="0"></td>
    <td><button type="button" class="btn btn-danger btn-sm removeVariant">Remove</button></td>
  </tr>
</template>
