@extends('admin.layouts.app')
@section('title', 'Coupons')

@section('content')

<style>
.codepill{display:inline-block;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:13.5px;font-weight:700;letter-spacing:.04em;background:var(--navy);color:#fff;padding:4px 10px;border-radius:3px}
.badge.state-live{background:#E5F3EB;color:var(--ok)}
.badge.state-scheduled{background:#E7EEF9;color:var(--navy)}
.badge.state-expired{background:#EEF0F3;color:var(--ink-mute)}
.badge.state-disabled{background:#EEF0F3;color:var(--ink-mute)}
.badge.state-usedup{background:#FBE9E8;color:var(--bad)}
.usebar{height:5px;background:var(--line);border-radius:3px;overflow:hidden;margin-top:6px;max-width:110px;margin-left:auto}
.usebar i{display:block;height:100%;background:var(--gold)}
</style>

<div class="panel">
  <div class="panel-head">
    <h2>{{ $coupons->total() }} coupons</h2>
    <a href="{{ route('admin.coupons.create') }}" class="btn btn-gold btn-sm">Add coupon</a>
  </div>

  <div class="panel-body" style="border-bottom:1px solid var(--line)">
    <form method="GET" class="filters">
      <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by code">
      <button class="btn btn-navy btn-sm">Search</button>
      @if (request('search'))
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-line btn-sm">Clear</a>
      @endif
    </form>
  </div>

  @if ($coupons->isEmpty())
    <div class="empty">
      <b>No coupons yet</b>
      Create one and share the code on your Facebook page.
    </div>
  @else
    <table>
      <thead>
        <tr>
          <th>Code</th><th>Discount</th><th>Applies to</th><th>Conditions</th>
          <th class="right">Used</th><th class="right">Given away</th>
          <th>Status</th><th class="right">Actions</th>
        </tr>
      </thead>
      <tbody>
      @foreach ($coupons as $coupon)
        <tr>
          <td>
            <span class="codepill">{{ $coupon->code }}</span>
            @if ($coupon->description)<div class="sub" style="margin-top:5px">{{ $coupon->description }}</div>@endif
          </td>
          <td>
            <strong>{{ $coupon->value_label }}</strong>
            @if ($coupon->max_discount)
              <div class="sub">up to ৳{{ number_format($coupon->max_discount) }}</div>
            @endif
            @if ($coupon->free_shipping)<div class="sub">+ free delivery</div>@endif
          </td>
          <td class="sub">{{ $coupon->scope_label }}</td>
          <td class="sub">
            @if ($coupon->min_spend)Min ৳{{ number_format($coupon->min_spend) }}<br>@endif
            @if ($coupon->first_order_only)First order only<br>@endif
            @if ($coupon->usage_limit_per_customer){{ $coupon->usage_limit_per_customer }} per customer<br>@endif
            @if ($coupon->expires_at)Ends {{ $coupon->expires_at->format('j M Y') }}@endif
            @if (! $coupon->min_spend && ! $coupon->first_order_only && ! $coupon->usage_limit_per_customer && ! $coupon->expires_at)
              —
            @endif
          </td>
          <td class="right">
            {{ $coupon->used_count }}@if ($coupon->usage_limit) / {{ $coupon->usage_limit }}@endif
            @if ($coupon->usage_limit)
              <div class="usebar"><i style="width:{{ min(100, round($coupon->used_count / $coupon->usage_limit * 100)) }}%"></i></div>
            @endif
          </td>
          <td class="right">৳{{ number_format($coupon->saved_total ?? 0) }}</td>
          <td><span class="badge state-{{ str_replace(' ', '', $coupon->state) }}">{{ ucfirst($coupon->state) }}</span></td>
          <td class="right">
            <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-line btn-sm">Edit</a>
            <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" style="display:inline"
                  onsubmit="return confirm('Delete {{ $coupon->code }}?')">
              @csrf @method('DELETE')
              <button class="btn btn-danger btn-sm">Delete</button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>

    <div class="pager">{{ $coupons->links('vendor.pagination.amjr') }}</div>
  @endif
</div>
@endsection
