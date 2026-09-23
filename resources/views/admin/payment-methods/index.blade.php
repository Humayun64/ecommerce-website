@extends('admin.layouts.app')
@section('title', 'Payment methods')

@section('content')

<style>
.pm-name{font-weight:600;display:flex;align-items:center;gap:9px}
.pm-name img{height:20px;width:auto}
.pm-name small{display:block;font-weight:400;color:var(--ink-mute);font-size:12.5px;margin-top:2px}
.pm-code{font-family:ui-monospace,'SF Mono',Menlo,Consolas,monospace;font-size:12.5px;color:var(--ink-mute)}
.pm-acct{font-weight:600}
.pm-acct small{display:block;font-weight:400;color:var(--ink-mute);font-size:12.5px;margin-top:2px}
.pm-tag{display:inline-block;font-size:11.5px;font-weight:700;border-radius:999px;padding:3px 10px;white-space:nowrap}
.pm-t-manual{background:#E8EDF6;color:var(--navy)}
.pm-t-cod{background:#E4F3EA;color:#14663E}
.pm-t-gateway{background:#FDF3DC;color:#8A6410}
.pm-dot{display:inline-flex;align-items:center;gap:7px;font-size:13px;font-weight:600}
.pm-dot i{width:8px;height:8px;border-radius:50%;background:var(--ok);display:block}
.pm-dot.off{color:var(--ink-mute)}
.pm-dot.off i{background:#C2C8D2}
.pm-star{color:var(--gold);font-size:12px;font-weight:700}
</style>

<div class="panel">
  <div class="panel-head">
    <div>
      <h2>Payment methods</h2>
      <div class="sub">What customers can choose at checkout, and the numbers they send money to.</div>
    </div>
    <a href="{{ route('admin.payment-methods.create') }}" class="btn btn-navy btn-sm">Add method</a>
  </div>

  @if ($methods->isEmpty())
    <div class="empty">
      <b>No payment methods</b>
      Add at least one, or nobody can check out.
    </div>
  @else
    <table>
      <thead>
        <tr>
          <th>Method</th>
          <th>Type</th>
          <th>Send money to</th>
          <th class="right">Used</th>
          <th>Status</th>
          <th class="right"></th>
        </tr>
      </thead>
      <tbody>
      @foreach ($methods as $method)
        <tr>
          <td>
            <div class="pm-name">
              @if ($method->logo)<img src="{{ Storage::url($method->logo) }}" alt="">@endif
              {{ $method->name }}
              @if ($method->is_default)<span class="pm-star">DEFAULT</span>@endif
            </div>
            <small class="pm-code">{{ $method->code }}</small>
            @if ($method->tagline)<small style="display:block;color:var(--ink-mute);font-size:12.5px;margin-top:3px">{{ $method->tagline }}</small>@endif
          </td>

          <td>
            <span class="pm-tag pm-t-{{ $method->driver }}">
              {{ $method->driver === 'cod' ? 'Cash on delivery' : ucfirst($method->driver) }}
            </span>
            @if ($method->driver === 'gateway')
              <small style="display:block;color:var(--ink-mute);font-size:12px;margin-top:4px">
                {{ $method->gateway ? \App\Models\PaymentMethod::GATEWAYS[$method->gateway] : 'No provider' }} — not connected
              </small>
            @endif
          </td>

          <td class="pm-acct">
            {{ $method->account_number ?: '—' }}
            @if ($method->account_type)<small>{{ $method->account_type }}</small>@endif
          </td>

          <td class="right sub">{{ $method->payments_count }}</td>

          <td>
            @if ($method->is_usable)
              <span class="pm-dot"><i></i> On</span>
            @elseif ($method->is_active && $method->driver === 'gateway')
              <span class="pm-dot off"><i></i> Waiting on the API</span>
            @else
              <span class="pm-dot off"><i></i> Off</span>
            @endif

            @if ($method->charge_percent > 0)
              <small style="display:block;color:var(--ink-mute);font-size:12px;margin-top:4px">
                +{{ rtrim(rtrim(number_format($method->charge_percent, 2), '0'), '.') }}% charge
              </small>
            @endif
          </td>

          <td class="right">
            <a href="{{ route('admin.payment-methods.edit', $method) }}" class="btn btn-line btn-sm">Edit</a>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  @endif
</div>

<div class="note" style="margin-top:18px">
  <b>About the online gateways.</b>
  SSLCommerz, and the bKash and Nagad APIs, have a place set aside here but no driver behind them
  yet — a method set to <em>Automatic</em> will not appear at checkout however complete it looks,
  so a customer can never think they paid online when nothing was taken. Everything you take today
  goes through <em>Manual</em>: the customer sends the money themselves and types the transaction ID,
  and you confirm it on the Payments screen.
</div>

@endsection
