@extends('frontend.layouts.master')
@section('main-content')
    <div class="title">
        <p>結帳</p>
    </div>
    <div class="content">
        <form id="form-checkout" method="POST" action="{{route('order.store')}}">
            @csrf
            <table class="table table-cart">
                <thead>
                    <tr>
                        <th>商品</th>
                        <th>單價</th>
                        <th>數量</th>
                        <th>金額</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($carts as $cart)
                        <tr>
                            <input type="hidden" name="product_id[]" value="{{$cart->product_id}}">
                            <input type="hidden" name="quantity[]" value="{{$cart->quantity}}">
                            <td><p class="product-title">{{$cart->title}}</p></td>
                            <td><p class="text-center">${{$cart->price}}</p></td>
                            <td><p class="text-center">{{$cart->quantity}}</p></td>
                            <td><p class="text-center product-amount">${{$cart->amount}}</p></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="grid-container">
                <div class="note">
                    <label>備註:</label>
                    <input type="text" name="note">
                </div>
                <div class="amount">
                    <label class="m-r-m">合計:</label> 
                    <p class="text-danger">${{$subTotal}}</p>
                </div>
                <div class="receiver-info">
                    <label>收件人<span>*</span></label> 
                    <input type="text" name="name" value="{{Auth::user()->name}}" required="required">
                    @error('name')
                        <span class="error">{{$message}}</span>
                    @enderror

                    <label>手機<span>*</span></label> 
                    <input type="text" name="cellphone" value="{{Auth::user()->cellphone}}" required="required">
                    @error('cellphone')
                        <span class="error">{{$message}}</span>
                    @enderror

                    <label>收件地址<span>*</span></label> 
                    <input type="text" name="address" value="{{Auth::user()->address}}" required="required">
                    @error('address')
                        <span class="error">{{$message}}</span>
                    @enderror
                </div>
                <div class="payments">
                    <label>付款方式</label>
                    <div class="nav-tabs">
                        <button class="btn active" type="button">貨到付款</button>
                        <!-- <button class="btn btn-prohibit" type="button">信用卡</button> -->
                    </div>
                    <input type="hidden" id="paymentMethod" name="paymentMethod" value="COD">
                </div>
                <div class="total-amount">
                    <div>
                        <label class="m-r-m">商品:</label>
                        <p>${{$subTotal}}</p>
                    </div>
                    <div>
                        <label class="-m-r-m">運費:</label>
                        <p>${{$homeDeliveryFee}}</p>
                    </div>
                    <div>
                        <label class="m-r-m">應付:</label>
                        <p class="text-danger">${{$subTotal+$homeDeliveryFee}}</p>
                    </div>
                    <input type="hidden" name="subTotal" value="{{$subTotal}}">
                    <input type="hidden" name="shippingFee" value="{{$homeDeliveryFee}}">
                    <input type="hidden" name="totalAmount" value="{{$subTotal+$homeDeliveryFee}}">
                </div>
                <div class="button">
                    <input type="hidden" name="fromCart" value="{{$fromCart}}">
                    <button id="checkout" class="btn right btn-dark" type="button">結帳</button>
                </div>
            </div>
        </form>
    </div>
@endsection
