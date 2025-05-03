@extends('frontend.layouts.master')
@section('main-content')
@include('frontend.layouts.order_status')
@include('frontend.layouts.order_review')
    <div class="content">
        <div class="order-header">
            @if($type=='unhandled')
                <p class="order_number">訂單編號: {{$order->order_number}}</p><p class="order_status">訂單狀態: </p><p class="status text-danger">待出貨</p>
            @elseif($type=='shipping')
                <p class="order_number">訂單編號: {{$order->order_number}}</p><p class="order_status">訂單狀態: </p><p class="status text-primary">運送中</p>
            @elseif($type=='completed')
                <p class="order_number">訂單編號: {{$order->order_number}}</p><p class="order_status">訂單狀態: </p><p class="status text-success">已完成</p>
            @elseif($type=='cancel')
                <p class="order_number">訂單編號: {{$order->order_number}}</p><p class="order_status">訂單狀態: </p><p class="status">已取消</p>
            @endif
        </div>
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
                @foreach($order['order_details'] as $detail)
                    <tr>
                        <td><a href="{{route('product-detail', $detail->slug)}}" class="product-title">{{$detail->title}}</a></td>
                        <td><p class="text-center">${{$detail->price}}</p></td>
                        <td><p class="text-center">{{$detail->quantity}}</p></td>
                        <td><p class="text-center">${{$detail->amount}}</p></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="order-info">
            <div>
                <label>備註:</label>
                <p class="text-danger">{{$order->note}}</p>
            </div>
            <div class="amount">
                <label class="m-r-m">合計:</label> 
                <p class="text-danger">${{$order->sub_total}}</p>
            </div>
            <div class="receiver-info">
                <label>收件人:</label> 
                {{$order->name}}

                <label>手機:</label> 
                {{$order->phone}}

                <label>收件地址:</label> 
                {{$order->address}}
            </div>
            <div class="payments">
                <label>付款方式:</label>
                @if($order->payment_method === 'COD')
                    <p>貨到付款</p>
                @elseif($order->payment_method === 'creditCard')
                    <p>信用卡</p>
                @endif
            </div>
            <div class="total-amount">
                <div>
                    <label class="m-r-m">商品:</label>
                    <p>${{$order->sub_total}}</p>
                </div>
                <div>
                    <label class="m-r-m">運費:</label>
                    <p>${{$order->shipping_fee}}</p>
                </div>
                <div>
                    <label class="m-r-m">應付:</label>
                    <p class="text-danger">${{$order->total_amount}}</p>
                </div>
            </div>
            <div class="button">
                @if($type=='unhandled')
                    <form method="GET" action="{{route('to-cancel', $order->id)}}">
                        @csrf
                        <button class="btn right btn-dark">取消訂單</button>
                    </form>
                @elseif($type=='shipping')
                    <form method="GET" action="{{route('to-completed', $order->id)}}">
                        @csrf
                        <button class="btn right btn-accent">完成訂單</button>
                    </form>
                @elseif($type=='completed')
                    <button id="again" class="btn right btn-dark" data-order-id="{{$order->id}}">重新購買</button>
                    @if(!$order->isReview)
                        <button class="btn right m-r-s m-l-s btn-accent btn-review" data-order-id="{{$order->id}}">評價</button>
                    @endif
                @elseif($type=='cancel')
                    <button id="again" class="btn right btn-dark" data-order-id="{{$order->id}}">重新購買</button>
                @endif
            </div>
        </div>
    </div>
    <div class="hidden popup-content">
        <p>商品不存在</p>
    </div>
@endsection