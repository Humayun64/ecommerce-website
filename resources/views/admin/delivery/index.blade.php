@extends('admin.layouts.app')
@section('title', 'Delivery charges')

@section('content')

<style>
.dl-grid{display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:18px;align-items:start}
@media(max-width:1080px){.dl-grid{grid-template-columns:1fr}}
.dl-side{display:flex;flex-direction:column;gap:18px;position:sticky;top:82px}
@media(max-width:1080px){.dl-side{position:static;order:-1}}

.dl-matrix{width:100%;border-collapse:separate;border-spacing:0}
.dl-matrix th{text-align:left;font-size:12px;font-weight:700;color:var(--ink-mute);padding:0 10px 10px 0;background:none;border:0}
.dl-matrix th.zone{text-align:center}
.dl-matrix td{padding:0 10px 10px 0;border:0;vertical-align:middle}
.dl-matrix tr:hover{background:none}
.dl-tier b{display:block;font-size:14.5px}
.dl-tier small{display:block;font-size:12px;color:var(--ink-mute);margin-top:2px}
.dl-money{position:relative}
.dl-money span{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--ink-mute);font-size:14px;pointer-events:none}
.dl-money input{width:100%;min-width:92px;border:1.5px solid var(--line);border-radius:3px;padding:10px 10px 10px 26px;font:inherit;font-size:14.5px;text-align:right;background:#fff}
.dl-money input:focus{border-color:var(--navy);outline:none}
.dl-money input::placeholder{color:#C3C8D2}

.dl-free{background:#E9F5EE;border:1px solid #BEE3CE;border-radius:4px;padding:16px 18px;display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-top:6px}
.dl-free b{display:block;font-size:14.5px;color:#12603A}
.dl-free small{display:block;font-size:12.5px;color:#2C7A52;margin-top:2px}
.dl-free .dl-money input{min-width:130px;background:#fff}

.dl-preview{font-size:13.5px}
.dl-zonehead{font-size:11.5px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--gold-deep);margin:14px 0 6px}
.dl-zonehead:first-child{margin-top:0}
.dl-row{display:flex;justify-content:space-between;gap:12px;padding:7px 0;border-bottom:1px solid var(--line)}
.dl-row:last-child{border-bottom:0}
.dl-row b{font-family:var(--display);font-variation-settings:"wdth" 110;font-weight:700;color:var(--navy)}
.dl-note{background:var(--gold-pale);color:var(--gold-deep);border-radius:3px;padding:10px 12px;font-size:12.5px;font-weight:600;margin-top:12px}

.dl-list{width:100%;border-collapse:collapse;margin-top:10px}
.dl-list td{padding:9px 10px 9px 0;border-bottom:1px solid var(--line);font-size:13.5px;vertical-align:middle}
.dl-list tr:last-child td{border-bottom:0}
.dl-list input[type=text],.dl-list input[type=number],.dl-list select{width:100%;border:1.5px solid var(--line);border-radius:3px;padding:7px 9px;font:inherit;font-size:13.5px;background:#fff}
.dl-list input:focus,.dl-list select:focus{border-color:var(--navy);outline:none}
.dl-inline{display:flex;gap:6px;align-items:center}
.dl-addrow{background:#FAFBFC}
.dl-prodname{font-weight:600}
.dl-prodname small{display:block;font-weight:400;color:var(--ink-mute);font-size:12px;margin-top:2px}
</style>

<div class="dl-grid">
<div style="display:flex;flex-direction:column;gap:18px;min-width:0">

  {{-- ---------- the matrix ---------- --}}
  <form method="POST" action="{{ route('admin.delivery.rates') }}">
    @csrf
    <div class="panel">
      <div class="panel-head">
        <div>
          <h2>Delivery rates by size and area</h2>
          <div class="sub">Set what each size of parcel costs to each part of the country.</div>
        </div>
        <button type="submit" class="btn btn-gold btn-sm">Save rates</button>
      </div>

      <div class="panel-body">
        @if ($zones->isEmpty() || $tiers->isEmpty())
          <div class="empty" style="padding:26px">
            <b>Nothing to price yet</b>
            Add at least one size band and one delivery area below.
          </div>
        @else
          <div style="overflow-x:auto">
            <table class="dl-matrix">
              <thead>
                <tr>
                  <th style="min-width:190px">Product size</th>
                  @foreach ($zones as $zone)
                    <th class="zone" style="min-width:120px">
                      {{ $zone->name }}
                      @unless ($zone->is_active)<div class="sub" style="font-weight:400">switched off</div>@endunless
                    </th>
                  @endforeach
                </tr>
              </thead>
              <tbody>
              @foreach ($tiers as $tier)
                <tr>
                  <td class="dl-tier">
                    <b>{{ $tier->icon }} {{ $tier->name }}
                      @if ($tier->is_default)<span class="badge b-gold" style="margin-left:6px">Default</span>@endif
                    </b>
                    @if ($tier->description)<small>{{ $tier->description }}</small>@endif
                  </td>
                  @foreach ($zones as $zone)
                    <td>
                      <label class="dl-money">
                        <span>৳</span>
                        <input type="number" step="1" min="0" name="rates[{{ $tier->id }}][{{ $zone->id }}]"
                               value="{{ $matrix[$tier->id][$zone->id] !== null ? (int) $matrix[$tier->id][$zone->id] : '' }}"
                               placeholder="{{ (int) $zone->rate }}">
                      </label>
                    </td>
                  @endforeach
                </tr>
              @endforeach
              </tbody>
            </table>
          </div>

          <div class="hint" style="margin-top:4px">
            Leave a box empty and that combination falls back to the area's own base rate.
          </div>

          <div class="dl-free">
            <div>
              <b>Free delivery above</b>
              <small>Orders over this amount ship free anywhere. Set 0 to switch it off.</small>
            </div>
            <label class="dl-money">
              <span>৳</span>
              <input type="number" step="1" min="0" name="free_delivery_over"
                     value="{{ (int) ($settings['free_delivery_over'] ?? 0) }}">
            </label>
          </div>

          <div class="divider"><span>When someone buys more than one thing</span></div>

          <div class="row2">
            <div class="field">
              <label for="delivery_multi_rule">Charge them</label>
              <select id="delivery_multi_rule" name="delivery_multi_rule">
                @foreach ($rules as $key => $label)
                  <option value="{{ $key }}" @selected(($settings['delivery_multi_rule'] ?? 'highest') === $key)>{{ $label }}</option>
                @endforeach
              </select>
              <div class="hint">One parcel usually costs one delivery, so "largest item only" is the honest default.</div>
            </div>
            <div class="field">
              <label for="delivery_extra_per_item">Extra per additional item (৳)</label>
              <input type="number" step="1" min="0" id="delivery_extra_per_item" name="delivery_extra_per_item"
                     value="{{ (int) ($settings['delivery_extra_per_item'] ?? 0) }}">
              <div class="hint">Only used by the third option.</div>
            </div>
          </div>

          <button type="submit" class="btn btn-gold">Save rates</button>
        @endif
      </div>
    </div>
  </form>

  {{-- ---------- size bands ---------- --}}
  <div class="panel">
    <div class="panel-head">
      <h2>Size bands</h2>
      <span class="sub">Group products by how big the parcel is</span>
    </div>
    <div class="panel-body">
      <table class="dl-list">
        <tbody>
        @foreach ($tiers as $tier)
          <tr>
            <td colspan="4">
              <form method="POST" action="{{ route('admin.delivery.tiers.update', $tier) }}" class="dl-inline">
                @csrf @method('PATCH')
                <input type="text" name="icon" value="{{ $tier->icon }}" style="width:58px;text-align:center" aria-label="Icon">
                <input type="text" name="name" value="{{ $tier->name }}" style="max-width:150px" required aria-label="Name">
                <input type="text" name="description" value="{{ $tier->description }}" placeholder="What goes in this band">
                <input type="number" name="sort_order" value="{{ $tier->sort_order }}" style="width:70px" aria-label="Order">
                <label class="check" style="white-space:nowrap">
                  <input type="checkbox" name="is_default" value="1" @checked($tier->is_default)> Default
                </label>
                <button class="btn btn-line btn-sm">Save</button>
              </form>
            </td>
            <td style="width:90px;text-align:right">
              <form method="POST" action="{{ route('admin.delivery.tiers.destroy', $tier) }}"
                    onsubmit="return confirm('Remove the {{ $tier->name }} band?')">
                @csrf @method('DELETE')
                <button class="btn btn-danger btn-sm">Remove</button>
              </form>
            </td>
          </tr>
        @endforeach

        <tr class="dl-addrow">
          <td colspan="5">
            <form method="POST" action="{{ route('admin.delivery.tiers.store') }}" class="dl-inline">
              @csrf
              <input type="text" name="icon" placeholder="📦" style="width:58px;text-align:center" aria-label="Icon">
              <input type="text" name="name" placeholder="New band name" style="max-width:150px" required>
              <input type="text" name="description" placeholder="What goes in it">
              <input type="number" name="sort_order" placeholder="0" style="width:70px">
              <button class="btn btn-navy btn-sm">Add band</button>
            </form>
          </td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>

  {{-- ---------- zones ---------- --}}
  <div class="panel">
    <div class="panel-head">
      <h2>Delivery areas</h2>
      <span class="sub">What the customer picks at checkout</span>
    </div>
    <div class="panel-body">
      <table class="dl-list">
        <tbody>
        @foreach ($zones as $zone)
          <tr>
            <td colspan="4">
              <form method="POST" action="{{ route('admin.delivery.zones.update', $zone) }}" class="dl-inline">
                @csrf @method('PATCH')
                <input type="text" name="name" value="{{ $zone->name }}" style="max-width:180px" required aria-label="Area name">
                <input type="text" name="delivery_time" value="{{ $zone->delivery_time }}" placeholder="How long it takes">
                <label class="dl-money" style="width:110px"><span>৳</span>
                  <input type="number" step="1" min="0" name="rate" value="{{ (int) $zone->rate }}" aria-label="Base rate">
                </label>
                <input type="number" name="sort_order" value="{{ $zone->sort_order }}" style="width:70px" aria-label="Order">
                <label class="check" style="white-space:nowrap">
                  <input type="checkbox" name="is_active" value="1" @checked($zone->is_active)> On
                </label>
                <button class="btn btn-line btn-sm">Save</button>
              </form>
            </td>
            <td style="width:90px;text-align:right">
              <form method="POST" action="{{ route('admin.delivery.zones.destroy', $zone) }}"
                    onsubmit="return confirm('Remove {{ $zone->name }}?')">
                @csrf @method('DELETE')
                <button class="btn btn-danger btn-sm">Remove</button>
              </form>
            </td>
          </tr>
        @endforeach

        <tr class="dl-addrow">
          <td colspan="5">
            <form method="POST" action="{{ route('admin.delivery.zones.store') }}" class="dl-inline">
              @csrf
              <input type="text" name="name" placeholder="New area name" style="max-width:180px" required>
              <input type="text" name="delivery_time" placeholder="Two to three days">
              <label class="dl-money" style="width:110px"><span>৳</span>
                <input type="number" step="1" min="0" name="rate" value="0" required aria-label="Base rate">
              </label>
              <input type="number" name="sort_order" placeholder="0" style="width:70px">
              <label class="check" style="white-space:nowrap">
                <input type="checkbox" name="is_active" value="1" checked> On
              </label>
              <button class="btn btn-navy btn-sm">Add area</button>
            </form>
          </td>
        </tr>
        </tbody>
      </table>

      <div class="hint" style="margin-top:10px">
        The base rate is the fallback used when a size band has no price set for that area.
      </div>
    </div>
  </div>

  {{-- ---------- product sizes ---------- --}}
  <form method="POST" action="{{ route('admin.delivery.products') }}">
    @csrf
    <div class="panel">
      <div class="panel-head">
        <div>
          <h2>Which size is each product?</h2>
          <div class="sub">
            @if ($unassigned)
              {{ $unassigned }} product{{ $unassigned > 1 ? 's have' : ' has' }} no band and will be charged at the default.
            @else
              Every product has a band.
            @endif
          </div>
        </div>
        <button type="submit" class="btn btn-gold btn-sm">Save sizes</button>
      </div>

      <table>
        <thead><tr><th>Product</th><th style="width:220px">Size band</th></tr></thead>
        <tbody>
        @foreach ($products as $product)
          <tr>
            <td class="dl-prodname">
              {{ $product->name }}
              <small>{{ $product->category?->name ?? 'Uncategorised' }} · {{ $product->sku }}</small>
            </td>
            <td>
              <select name="tier[{{ $product->id }}]" style="width:100%;border:1.5px solid var(--line);border-radius:3px;padding:8px 10px;font:inherit;font-size:14px;background:#fff">
                <option value="">Use the default band</option>
                @foreach ($tiers as $tier)
                  <option value="{{ $tier->id }}" @selected($product->delivery_tier_id == $tier->id)>{{ $tier->icon }} {{ $tier->name }}</option>
                @endforeach
              </select>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>

      <div class="panel-body">
        <button type="submit" class="btn btn-gold">Save sizes</button>
      </div>
    </div>
  </form>

</div>

{{-- ---------- live preview ---------- --}}
<div class="dl-side">
  <div class="panel">
    <div class="panel-head"><h2>What customers see</h2></div>
    <div class="panel-body dl-preview">
      @forelse ($zones->where('is_active', true) as $zone)
        <div class="dl-zonehead">{{ $zone->name }}</div>
        @foreach ($tiers as $tier)
          @php $rate = $matrix[$tier->id][$zone->id] ?? null; @endphp
          <div class="dl-row">
            <span>{{ $tier->icon }} {{ $tier->name }}</span>
            <b>৳{{ number_format($rate !== null ? $rate : $zone->rate) }}</b>
          </div>
        @endforeach
      @empty
        <div class="sub">No delivery areas are switched on.</div>
      @endforelse

      @if (($settings['free_delivery_over'] ?? 0) > 0)
        <div class="dl-note">Free delivery on orders above ৳{{ number_format($settings['free_delivery_over']) }}</div>
      @endif
    </div>
  </div>

  <div class="panel">
    <div class="panel-head"><h2>How it works</h2></div>
    <div class="panel-body">
      <ul style="margin:0;padding-left:18px;font-size:13.5px;line-height:1.75;color:var(--ink-mute)">
        <li>Each product is put in a size band</li>
        <li>The customer picks their area at checkout</li>
        <li>The charge comes from that band and that area</li>
        <li>Baskets with several items follow the rule you set above</li>
        <li>Orders above the free threshold ship free anywhere</li>
      </ul>
    </div>
  </div>
</div>
</div>
@endsection
