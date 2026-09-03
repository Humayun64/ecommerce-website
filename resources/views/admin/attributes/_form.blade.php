@csrf
@if ($method === 'PUT') @method('PUT') @endif

@if ($errors->any())
  <div class="alert alert-bad">
    <ul style="margin:0;padding-left:18px">
      @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
  </div>
@endif

<div class="row2">
  <div class="field">
    <label for="name">Attribute name</label>
    <input type="text" id="name" name="name" value="{{ old('name', $attribute->name) }}" required placeholder="Size">
    <div class="hint">Singular. Size, not Sizes.</div>
  </div>
  <div class="field">
    <label for="sort_order">Sort order</label>
    <input type="number" id="sort_order" name="sort_order" min="0" value="{{ old('sort_order', $attribute->sort_order ?? 0) }}">
    <div class="hint">Decides the order in variation labels: "Shade 21 / 30 ml".</div>
  </div>
</div>

<div class="field">
  <label class="check">
    <input type="checkbox" name="is_filterable" value="1" @checked(old('is_filterable', $attribute->is_filterable ?? true))>
    Let customers filter by this on category pages
  </label>
</div>

<div class="divider"><span>Values</span></div>

<div class="vtable-wrap">
  <table class="vtable">
    <thead><tr><th style="min-width:220px">Value</th><th style="width:110px"></th></tr></thead>
    <tbody id="valueRows">
      @php $rows = old('values', $attribute->values->toArray() ?? []); @endphp
      @foreach ($rows as $i => $row)
        <tr>
          <td>
            <input type="hidden" name="values[{{ $i }}][id]" value="{{ $row['id'] ?? '' }}">
            <input type="text" name="values[{{ $i }}][value]" value="{{ $row['value'] ?? '' }}" placeholder="30 ml">
          </td>
          <td><button type="button" class="btn btn-danger btn-sm removeValue">Remove</button></td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

<button type="button" class="btn btn-line btn-sm" id="addValue" style="margin-top:10px">Add a value</button>

<p class="sub" style="margin:14px 0 0">
  A value already used by a product variation will not be deleted even if you remove the row —
  that would break the variation's identity. Remove it from the products first.
</p>

<div class="form-actions" style="margin-top:22px">
  <button type="submit" class="btn btn-gold">{{ $submit }}</button>
  <a href="{{ route('admin.attributes.index') }}" class="btn btn-line">Cancel</a>
</div>

<template id="valueTemplate">
  <tr>
    <td>
      <input type="hidden" name="values[__i__][id]" value="">
      <input type="text" name="values[__i__][value]" placeholder="30 ml">
    </td>
    <td><button type="button" class="btn btn-danger btn-sm removeValue">Remove</button></td>
  </tr>
</template>
