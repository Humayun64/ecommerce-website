@csrf
@if ($method === 'PUT') @method('PUT') @endif

<div class="row2">
  <div class="field">
    <label for="name">Name</label>
    <input type="text" id="name" name="name" value="{{ old('name', $brand->name) }}" required>
    @error('name') <div class="err">{{ $message }}</div> @enderror
  </div>

  <div class="field">
    <label for="slug">Slug</label>
    <input type="text" id="slug" name="slug" value="{{ old('slug', $brand->slug) }}" placeholder="Leave blank to generate">
    @error('slug') <div class="err">{{ $message }}</div> @enderror
  </div>
</div>

<div class="row2">
  <div class="field">
    <label for="country">Country of origin</label>
    <input type="text" id="country" name="country" value="{{ old('country', $brand->country) }}" placeholder="South Korea">
    <div class="hint">Shown as the origin tag on product cards.</div>
  </div>

  <div class="field">
    <label for="sort_order">Sort order</label>
    <input type="number" id="sort_order" name="sort_order" min="0" value="{{ old('sort_order', $brand->sort_order ?? 0) }}">
  </div>
</div>

<div class="field">
  <label for="description">Description</label>
  <textarea id="description" name="description">{{ old('description', $brand->description) }}</textarea>
</div>

<div class="field">
  <label class="check">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $brand->is_active))>
    Show this brand on the storefront
  </label>
</div>

<div class="form-actions">
  <button type="submit" class="btn btn-gold">{{ $submit }}</button>
  <a href="{{ route('admin.brands.index') }}" class="btn btn-line">Cancel</a>
</div>
