<div class="logout">
    <input type="button" class="btn-logout right" value="登出" onclick="location.href='{{route('logout')}}'">
</div>
@php
    if (!isset($productCol))
        $productCol = $unlistedCol = $unhandledCol = $shippingCol = $completedCol = $cancelCol = 'col';
@endphp
<div class="topbar">
    <div class="row">
        <a href="/admin/product"><div class="{{ $productCol }}"><p>商品(<p>{{Helper::getProductCount('active')}}</p>)</p></div></a>
        <a href="/admin/product?type=unlisted"><div class="{{ $unlistedCol }}"><p>已下架(<span>{{Helper::getProductCount('inactive')}}</span>)</p></div></a>
        <a href="/admin/order?type=unhandled"><div class="{{ $unhandledCol }}"><p>待出貨(<span class="count">{{Helper::getAdminOrdertCount('unhandled')}}</span>)</p></div></a>
        <a href="/admin/order?type=shipping"><div class="{{ $shippingCol }}"><p>待收貨(<span class="count">{{Helper::getAdminOrdertCount('shipping')}}</span>)</p></div></a>
        <a href="/admin/order?type=completed"><div class="{{ $completedCol }}"><p>已完成</p></div></a>
        <a href="/admin/order?type=cancel"><div class="{{ $cancelCol }}"><p>已取消</p></div></a>
    </div>
</div>