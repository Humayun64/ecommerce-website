@extends('admin.layouts.app')
@section('title', ($name ?: $phone))

@section('content')

@php
  $refusal = \App\Services\CustomerDirectory::refusalRate($delivered, $failed);
@endphp

<div class="form-grid">
<div class="form-main">

  @if ($profile->is_blocked)
    <div class="alert alert-bad">
      <strong>This customer is blocked.</strong>
      They cannot place new orders on this number.
      @if ($profile->block_reason) Reason: {{ $profile->block_reason }}.@endif
      @if ($profile->blocked_at) Blocked {{ $profile->blocked_at->format('j M Y') }}.@endif
    </div>
  @endif

  <div class="panel">
    <div class="panel-head">
      <div>
        <h2>{{ $name ?: 'Unnamed customer' }}</h2>
        <div class="sub">
          <a href="tel:{{ $phone }}">{{ $phone }}</a>
          @if ($email) · <a href="mailto:{{ $email }}">{{ $email }}</a>@endif
        </div>
      </div>
      <a href="{{ route('admin.customers.index') }}" class="btn btn-line btn-sm">All customers</a>
    </div>

    <div class="panel-body">
      <div class="stats" style="margin:0">
        <div class="stat">
          <div class="lab">Orders</div>
          <div class="num">{{ $orders->count() }}</div>
          <div class="foot">{{ $delivered }} delivered</div>
        </div>
        <div class="stat">
          <div class="lab">Spent</div>
          <div class="num">৳{{ number_format($spent) }}</div>
          <div class="foot">Cancelled orders excluded</div>
        </div>
        <div class="stat">
          <div class="lab">Average order</div>
          <div class="num">৳{{ number_format($orders->count() ? $spent / max(1, $orders->whereNotIn('status', ['cancelled','returned'])->count()) : 0) }}</div>
          <div class="foot">Across completed orders</div>
        </div>
        <div class="stat {{ $refusal !== null && $refusal >= 20 ? 'accent' : '' }}">
          <div class="lab">Refused or returned</div>
          <div class="num">{{ $refusal === null ? '—' : $refusal . '%' }}</div>
          <div class="foot">{{ $failed }} of {{ $delivered + $failed }} settled orders</div>
        </div>
      </div>

      @if ($refusal !== null && $refusal >= 40 && ($delivered + $failed) >= 3)
        <div class="note" style="margin-top:18px">
          This customer refuses a large share of their parcels. On cash on delivery you pay
          both courier legs each time, so it may be worth calling to confirm before shipping.
        </div>
      @endif
    </div>
  </div>

  <div class="panel">
    <div class="panel-head"><h2>Order history</h2></div>
    @if ($orders->isEmpty())
      <div class="empty"><b>No orders yet</b>This person has an account but has not bought anything.</div>
    @else
      <table>
        <thead><tr><th>Order</th><th>Area</th><th class="right">Items</th><th class="right">Total</th><th>Status</th><th class="right">Placed</th></tr></thead>
        <tbody>
        @foreach ($orders as $order)
          <tr>
            <td><a href="{{ route('admin.orders.show', $order) }}"><strong>{{ $order->order_number }}</strong></a></td>
            <td class="sub">{{ $order->shipping_zone_name ?? '—' }}</td>
            <td class="right">{{ $order->items_count }}</td>
            <td class="right">৳{{ number_format($order->total) }}</td>
            <td><span class="badge status-{{ $order->status }}">{{ $order->status_label }}</span></td>
            <td class="right sub">{{ $order->created_at->format('j M Y') }}</td>
          </tr>
        @endforeach
        </tbody>
      </table>
    @endif
  </div>

  @if ($reviews->isNotEmpty())
    <div class="panel">
      <div class="panel-head"><h2>Reviews written</h2></div>
      <table>
        <thead><tr><th>Product</th><th>Rating</th><th>Review</th><th>Status</th></tr></thead>
        <tbody>
        @foreach ($reviews as $review)
          <tr>
            <td class="sub">{{ $review->product?->name ?? '—' }}</td>
            <td style="color:var(--gold)">{{ str_repeat('★', $review->rating) }}</td>
            <td class="sub" style="max-width:340px">{{ Str::limit($review->body, 110) }}</td>
            <td><span class="badge {{ $review->status === 'approved' ? 'b-on' : 'b-off' }}">{{ \App\Models\Review::STATUSES[$review->status] ?? $review->status }}</span></td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  @endif

</div>

<div class="form-side">

  <div class="panel">
    <div class="panel-head"><h2>Account</h2></div>
    <div class="panel-body">
      @if ($user)
        <div class="glance"><span>Registered</span><b style="font-size:13px">{{ $user->created_at->format('j M Y') }}</b></div>
        <div class="glance"><span>Name on account</span><b style="font-size:13px">{{ $user->name }}</b></div>
        <div class="glance"><span>Email</span><b style="font-size:13px">{{ $user->email }}</b></div>
      @else
        <p class="sub" style="margin:0">Guest customer — no account on this number. Their orders are matched by phone.</p>
      @endif
    </div>
  </div>

  <form method="POST" action="{{ route('admin.customers.update', $phone) }}">
    @csrf @method('PATCH')

    <div class="panel">
      <div class="panel-head"><h2>Your notes</h2></div>
      <div class="panel-body">
        <div class="field">
          <textarea name="note" style="min-height:110px" placeholder="Anything worth remembering — preferred delivery time, past problems, who they are.">{{ old('note', $profile->note) }}</textarea>
          <div class="hint">Only you see this. It never reaches the customer.</div>
        </div>
        <button type="submit" name="action" value="save" class="btn btn-navy" style="width:100%">Save note</button>
      </div>
    </div>

    <div class="panel {{ $profile->is_blocked ? '' : 'dangerbox' }}">
      <div class="panel-head"><h2>{{ $profile->is_blocked ? 'Blocked' : 'Block this customer' }}</h2></div>
      <div class="panel-body">
        @if ($profile->is_blocked)
          <p class="sub" style="margin:0 0 14px">
            Orders on this number are refused at checkout, and you cannot place one by hand either.
          </p>
          <button type="submit" name="action" value="unblock" class="btn btn-gold" style="width:100%">Unblock</button>
        @else
          <p class="sub" style="margin:0 0 14px">
            Stops new orders from this number — the storefront and manual orders both. Use it for
            someone who keeps refusing parcels.
          </p>
          <div class="field">
            <label for="block_reason">Reason</label>
            <input type="text" id="block_reason" name="block_reason" placeholder="Refused three parcels in a row">
          </div>
          <button type="submit" name="action" value="block" class="btn btn-danger" style="width:100%"
                  onclick="return confirm('Block {{ $phone }} from ordering?')">Block this number</button>
        @endif
      </div>
    </div>
  </form>

</div>
</div>
@endsection
