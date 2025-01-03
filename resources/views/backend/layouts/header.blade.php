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
        <a href="/admin/product?type=unlisted"><div class="{{ $unlistedCol }}"><p>已下架(<p>{{Helper::getProductCount('inactive')}}</p>)</p></div></a>
        <a href="/admin/order?type=unhandled"><div class="{{ $unhandledCol }}"><p>待出貨(<p class="count">{{Helper::getAdminOrdertCount('unhandled')}}</p>)</p></div></a>
        <a href="/admin/order?type=shipping"><div class="{{ $shippingCol }}"><p>待收貨(<p class="count">{{Helper::getAdminOrdertCount('shipping')}}</p>)</p></div></a>
        <a href="/admin/order?type=completed"><div class="{{ $completedCol }}"><p>已完成</p></div></a>
        <a href="/admin/order?type=cancel"><div class="{{ $cancelCol }}"><p>已取消</p></div></a>
    </div>
</div>