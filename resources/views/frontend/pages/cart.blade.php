@extends('frontend.layouts.master')
@section('main-content')
    <div class="title">
        <p>購物車</p>
    </div>
    <div class="content">
        <form name="form" method="GET" action="">
            <div class="top check-all">
                <input type="checkbox" class="checkAll" checked>
                <label>全選</label>
            </div>
            <table class="table table-cart">
                <thead>
                    <tr>
                        <th>商品</th>
                        <th>單價</th>
                        <th>數量</th>
                        <th>金額</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @if(Helper::getAllProductFromCart()->isNotEmpty())
                        @foreach(Helper::getAllProductFromCart() as $cart)
                            <tr>
                                <td>
                                    <input type="checkbox" name="check[]" value="{{$cart->id}}" checked>
                                    <a class="product-title" href="{{route('product-detail', [$cart->product['id']])}}">{{$cart->product['title']}}</a>
                                </td>
                                <td ><p class="text-center">${{$cart->product['price']}}</p></td>
                                <td>
                                    <div class="text-center">
                                        <div class="stock" id="click">
                                            <div class="minus">
                                                <i class="ti-minus"></i>
                                                <input type='button' class='qtyminus' field="{{$cart->product_id}}">
                                            </div>
                                            <div id="product">
                                                <input type='text' name="{{$cart->product_id}}" value="{{$cart->quantity}}" class='qty' oninput="value=value.replace(/[^\d]/g,'')" 
                                                    data-stock="{{$cart->product['stock']}}" data-quantity="{{$cart->quantity}}" data-price="{{$cart->price}}" field="{{$cart->product_id}}">
                                            </div>
                                            <div class="plus">
                                                <i class="ti-plus"></i>
                                                <input type='button' class='qtyplus' field="{{$cart->product_id}}">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><p id="{{$cart->product_id}}" class="text-center product-amount">${{$cart->amount}}</p></td>
                                <td>
                                    <p class="text-center"><a class="product-delete" href="{{route('cart.destroy', [$cart->id])}}">刪除</a></p>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
            <div class="foot">
                <div class="check-all">
                    <input type="checkbox" class="checkAll2" checked>
                    <label>全選</label>
                </div>&emsp;
                <div class="btn-delete">
                    <button id="to-delete">刪除</button>
                </div>
            </div>
                @if(Helper::getAllProductFromCart()->isNotEmpty())
                    <div>
                        <button id="to-checkout" class="btn right btn-dark">去結帳</button>
                    </div>
                    <div>
                        <button class="btn right btn-prohibit hide" type="button">去結帳</button>
                    </div>
                @else
                    <div>
                        <button class="btn right btn-prohibit" type="button">去結帳</button>
                    </div>
                @endif
        </form>
    </div>
@endsection