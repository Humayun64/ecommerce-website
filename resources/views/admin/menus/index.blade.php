@extends('admin.layouts.app')
@section('title', 'Menus')

@section('content')

<style>
.mn-grid{display:grid;grid-template-columns:330px minmax(0,1fr);gap:18px;align-items:start}
@media(max-width:1000px){.mn-grid{grid-template-columns:1fr}}
.mn-list{display:flex;flex-direction:column;gap:8px}
.mn-item{display:flex;align-items:center;gap:12px;background:#fff;border:1.5px solid var(--line);border-radius:4px;padding:11px 14px}
.mn-item.dragging{opacity:.45;border-style:dashed}
.mn-item.over{border-color:var(--gold);box-shadow:0 0 0 3px var(--gold-pale)}
.mn-item.off{background:#FAFBFC}
.mn-item.off .mn-label{color:var(--ink-mute);text-decoration:line-through}
.mn-handle{cursor:grab;color:#B8BEC8;flex-shrink:0;line-height:1;font-size:15px;letter-spacing:1px;user-select:none}
.mn-handle:active{cursor:grabbing}
.mn-body{flex:1;min-width:0}
.mn-label{font-size:14.5px;font-weight:600}
.mn-url{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:12px;color:var(--ink-mute);word-break:break-all}
.mn-acts{display:flex;gap:5px;flex-shrink:0}
.mn-acts form{display:inline}
.mn-pos{width:30px;height:24px;border-radius:3px;background:var(--paper);color:var(--ink-mute);font-size:12px;font-weight:700;display:grid;place-items:center;flex-shrink:0}
.mn-col{margin-bottom:22px}
.mn-colhead{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:10px}
.mn-colhead h3{font-size:14px;margin:0}
.mn-edit{display:none;margin-top:10px;padding-top:10px;border-top:1px dashed var(--line)}
.mn-edit.open{display:block}
.mn-edit .row2{gap:10px}
.mn-sugg{display:flex;flex-wrap:wrap;gap:6px;margin-top:7px}
.mn-sugg button{border:1px solid var(--line);background:#fff;border-radius:3px;padding:4px 9px;font-size:12px;cursor:pointer;color:var(--ink-mute)}
.mn-sugg button:hover{border-color:var(--navy);color:var(--navy)}
</style>

<div class="tabs">
  @foreach (\App\Models\MenuItem::LOCATIONS as $key => $label)
    <a href="{{ route('admin.menus.index', ['location' => $key]) }}" class="tab {{ $location === $key ? 'on' : '' }}">{{ $label }}</a>
  @endforeach
</div>

<div class="mn-grid">

  {{-- ---------- add ---------- --}}
  <div>
    <div class="panel">
      <div class="panel-head"><h2>Add a link</h2></div>
      <div class="panel-body">
        <form method="POST" action="{{ route('admin.menus.store') }}">
          @csrf
          <input type="hidden" name="location" value="{{ $location }}">

          <div class="field">
            <label for="label">Label</label>
            <input type="text" id="label" name="label" required placeholder="Return policy">
          </div>

          <div class="field">
            <label for="url">Address</label>
            <input type="text" id="url" name="url" required placeholder="/return-policy">
            <div class="hint">Start with / for a page on this site, or paste a full https:// link.</div>
            <div class="mn-sugg">
              <button type="button" data-url="/">Home</button>
              <button type="button" data-url="/shop">Shop</button>
              <button type="button" data-url="/track">Track order</button>
              @foreach ($pages as $page)
                <button type="button" data-url="/{{ $page->slug }}" data-label="{{ $page->title }}">{{ $page->title }}</button>
              @endforeach
            </div>
          </div>

          @if ($location === 'footer')
            <div class="field">
              <label for="column">Column</label>
              <select id="column" name="column">
                <option value="1">{{ $settings['footer_col1_title'] ?? 'Column 1' }}</option>
                <option value="2">{{ $settings['footer_col2_title'] ?? 'Column 2' }}</option>
              </select>
            </div>
          @endif

          <div class="field">
            <label class="check"><input type="checkbox" name="is_active" value="1" checked> Show it</label>
          </div>
          <div class="field">
            <label class="check"><input type="checkbox" name="new_tab" value="1"> Open in a new tab</label>
          </div>

          <button type="submit" class="btn btn-gold" style="width:100%">Add to menu</button>
        </form>
      </div>
    </div>

    @if ($location === 'footer')
      <div class="panel" style="margin-top:18px">
        <div class="panel-head"><h2>Column headings</h2></div>
        <div class="panel-body">
          <form method="POST" action="{{ route('admin.menus.columns') }}">
            @csrf
            <div class="field">
              <label for="footer_col1_title">First column</label>
              <input type="text" id="footer_col1_title" name="footer_col1_title" value="{{ $settings['footer_col1_title'] ?? 'Shop' }}">
            </div>
            <div class="field">
              <label for="footer_col2_title">Second column</label>
              <input type="text" id="footer_col2_title" name="footer_col2_title" value="{{ $settings['footer_col2_title'] ?? 'Your order' }}">
            </div>
            <button type="submit" class="btn btn-navy" style="width:100%">Save headings</button>
          </form>
        </div>
      </div>
    @endif
  </div>

  {{-- ---------- current items ---------- --}}
  <div>
    @php $columns = $location === 'footer' ? [1, 2] : [1]; @endphp

    @foreach ($columns as $column)
      @php $columnItems = $items->where('column', $column); @endphp

      <div class="panel mn-col">
        <div class="panel-head">
          <div>
            <h2>
              @if ($location === 'footer')
                {{ $settings['footer_col' . $column . '_title'] ?? 'Column ' . $column }}
              @else
                Menu items
              @endif
            </h2>
            <div class="sub">{{ $columnItems->count() }} link{{ $columnItems->count() === 1 ? '' : 's' }} · drag to reorder</div>
          </div>
        </div>

        <div class="panel-body">
          @if ($columnItems->isEmpty())
            <div class="empty" style="padding:26px"><b>Nothing here yet</b>Add a link on the left.</div>
          @else
            <div class="mn-list" data-sortable data-url="{{ route('admin.menus.reorder') }}">
              @foreach ($columnItems as $item)
                <div class="mn-item {{ $item->is_active ? '' : 'off' }}" draggable="true" data-id="{{ $item->id }}">
                  <span class="mn-handle" title="Drag to reorder">⣿</span>
                  <span class="mn-pos">{{ $loop->iteration }}</span>

                  <div class="mn-body">
                    <div class="mn-label">{{ $item->label }}</div>
                    <div class="mn-url">{{ $item->url }}@if ($item->new_tab) · new tab @endif</div>

                    <div class="mn-edit" id="edit-{{ $item->id }}">
                      <form method="POST" action="{{ route('admin.menus.update', $item) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="location" value="{{ $item->location }}">
                        <div class="row2">
                          <div class="field" style="margin-bottom:10px">
                            <label>Label</label>
                            <input type="text" name="label" value="{{ $item->label }}" required>
                          </div>
                          <div class="field" style="margin-bottom:10px">
                            <label>Address</label>
                            <input type="text" name="url" value="{{ $item->url }}" required>
                          </div>
                        </div>
                        @if ($location === 'footer')
                          <div class="field" style="margin-bottom:10px">
                            <label>Column</label>
                            <select name="column">
                              <option value="1" @selected($item->column === 1)>{{ $settings['footer_col1_title'] ?? 'Column 1' }}</option>
                              <option value="2" @selected($item->column === 2)>{{ $settings['footer_col2_title'] ?? 'Column 2' }}</option>
                            </select>
                          </div>
                        @endif
                        <label class="check" style="margin-bottom:6px"><input type="checkbox" name="is_active" value="1" @checked($item->is_active)> Show it</label>
                        <label class="check" style="margin-bottom:10px"><input type="checkbox" name="new_tab" value="1" @checked($item->new_tab)> New tab</label>
                        <button class="btn btn-navy btn-sm">Save link</button>
                      </form>
                    </div>
                  </div>

                  <div class="mn-acts">
                    <button type="button" class="btn btn-line btn-sm" data-toggle="edit-{{ $item->id }}">Edit</button>
                    <form method="POST" action="{{ route('admin.menus.destroy', $item) }}"
                          onsubmit="return confirm('Remove {{ $item->label }}?')">
                      @csrf @method('DELETE')
                      <button class="btn btn-danger btn-sm">Remove</button>
                    </form>
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        </div>
      </div>
    @endforeach
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/menu-manager.js') }}"></script>
@endpush
