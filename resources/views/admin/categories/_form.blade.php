@csrf
@if ($method === 'PUT') @method('PUT') @endif

<div class="row2">
  <div class="field">
    <label for="name">Name</label>
    <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}" required>
    @error('name') <div class="err">{{ $message }}</div> @enderror
  </div>

  <div class="field">
    <label for="slug">Slug</label>
    <input type="text" id="slug" name="slug" value="{{ old('slug', $category->slug) }}" placeholder="Leave blank to generate">
    <div class="hint">Used in the URL. Changing it breaks existing links.</div>
    @error('slug') <div class="err">{{ $message }}</div> @enderror
  </div>
</div>

<div class="row2">
  <div class="field">
    <label for="parent_id">Parent category</label>
    <select id="parent_id" name="parent_id">
      <option value="">None — this is a top-level category</option>
      @foreach ($parents as $parent)
        <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id) == $parent->id)>{{ $parent->name }}</option>
      @endforeach
    </select>
  </div>

  <div class="field">
    <label for="sort_order">Sort order</label>
    <input type="number" id="sort_order" name="sort_order" min="0" value="{{ old('sort_order', $category->sort_order ?? 0) }}">
    <div class="hint">Lower numbers appear first in the menu.</div>
  </div>
</div>

<div class="field">
  <label for="description">Description</label>
  <textarea id="description" name="description">{{ old('description', $category->description) }}</textarea>
</div>

<div class="field">
  <label class="check">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active))>
    Show this category on the storefront
  </label>
</div>

<div class="form-actions">
  <button type="submit" class="btn btn-gold">{{ $submit }}</button>
  <a href="{{ route('admin.categories.index') }}" class="btn btn-line">Cancel</a>
</div>
