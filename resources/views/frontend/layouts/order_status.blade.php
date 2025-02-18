@php
    if (!isset($unhandledCol))
        $unhandledCol = $shippingCol = $completedCol = $cancelCol = 'col';
@endphp
<div class="topbar">
    <div class="row">
        <a href="/user/order?type=unhandled"><div class="{{ $unhandledCol }}"><p>待出貨(<span class="count">{{Helper::getOrderCount('unhandled')}}</span>)</p></div></a>
        <a href="/user/order?type=shipping"><div class="{{ $shippingCol }}"><p>待收貨(<span class="count">{{Helper::getOrderCount('shipping')}}</span>)</p></div></a>
        <a href="/user/order?type=completed"><div class="{{ $completedCol }}"><p>已完成</p></div></a>
        <a href="/user/order?type=cancel"><div class="{{ $cancelCol }}"><p>已取消</p></div></a>
    </div>
</div>