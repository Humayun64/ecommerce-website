{{--
  Order pipeline: one tile per status with the count, the money and its share.
  Needs $pipeTitle, $pipeSub, $pipeRows (status => ['n' => count, 'amount' => taka])
  and $pipeQuery (extra filters for the links, e.g. from / to dates).
--}}
@php
  $pipeTotal = max(1, collect($pipeRows)->sum('n'));
  $pipePlaced = collect($pipeRows)->sum('n');
@endphp
<div class="panel dash-panel os-panel">
  <div class="panel-head">
    <div>
      <h2>{{ $pipeTitle }}</h2>
      <span class="sub">{{ $pipeSub }}</span>
    </div>
    <div class="dash-chart-sum">
      <div><span>Placed</span><b>{{ number_format($pipePlaced) }}</b></div>
      <div><span>Order value</span><b>৳{{ number_format(collect($pipeRows)->sum('amount')) }}</b></div>
    </div>
  </div>
  <div class="panel-body">
    <div class="os-grid">
      @foreach (\App\Models\Order::STATUSES as $code => $name)
        @php
          $row   = $pipeRows[$code] ?? ['n' => 0, 'amount' => 0];
          $share = (int) round($row['n'] / $pipeTotal * 100);
        @endphp
        <a href="{{ route('admin.orders.index', array_merge(['status' => $code], $pipeQuery ?? [])) }}" class="os-tile os-{{ $code }} {{ $row['n'] ? '' : 'is-zero' }}">
          <span class="os-name"><i></i>{{ $name }}</span>
          <b class="os-num">{{ number_format($row['n']) }}</b>
          <span class="os-amt">৳{{ number_format($row['amount']) }}</span>
          <span class="os-bar"><i style="width:{{ $row['n'] ? max(4, $share) : 0 }}%"></i></span>
          <span class="os-share">{{ $share }}% of orders</span>
        </a>
      @endforeach
    </div>
  </div>
</div>
