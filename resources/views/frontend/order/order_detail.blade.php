@extends('frontend.layouts.master')
@section('main-content')
@include('frontend.layouts.order_status')
    <div id="hidden" class="popup-bg">
        <div class="review-popup">
            <form id="formsubmit" name="form" method="POST" action="{{route('review')}}">
                @csrf
                <input type="hidden" name="order_id" value="{{$order->id}}">
                @php
                    $index = 0;
                @endphp
                @foreach($order['order_details'] as $detail)
                    <div class="review">
                        <p>${{$detail->title}}</p>
                        <div class="rating-box" data-index="{{$index}}">
                            <span id="star-1" class="empty-stars"></span>
                            <span id="star-2" class="empty-stars"></span>
                            <span id="star-3" class="empty-stars"></span>
                            <span id="star-4" class="empty-stars"></span>
                            <span id="star-5" class="empty-stars"></span>
                        </div>
                        <div class="review-inner">
                            <input type="hidden" id="{{$index++}}" name="rate[]">
                            <textarea class="comment" name="review[]"></textarea>
                        </div>
                    </div>
                @endforeach
            </form>
            <div class="button">
                <button class="btn right btn-accent hide" form="formsubmit">送出</button>
                <button class="btn right btn-prohibit" type="button">送出</button>
                <button class="btn right btn-dark" type="button" onClick="act2();">取消</button>
            </div>
        </div>
    </div>
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
                <p>貨到付款</p>
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
            <a href="{{route('aaa', [$order->id])}}">aaa</a>
            <a href="{{route('bbb', [$order->id])}}">bbb</a>
                @if($type=='unhandled')
                    <form method="GET" action="{{route('to-cancel', $order->id)}}">
                        @csrf
                        <button class="btn right btn-dark">取消訂單</button>
                    </form>
                @elseif($type=='shipping')
                    <form method="GET" action="{{route('to-completed', $order->id)}}">
                        @csrf
                        <button class="btn right btn-accent" onClick="act3({{$order->id}});">完成訂單</button>
                    </form>
                @elseif($type=='completed')
                    <button class="btn right btn-dark" onClick="act3({{$order->id}});">重新購買</button>
                    <button class="btn right m-r-m btn-accent" onClick="act1();">評價</button>
                @elseif($type=='cancel')
                    <button class="btn right btn-dark" onClick="act3({{$order->id}});">重新購買</button>
                @endif
            </div>
        </div>
    </div>
    <div class="hidden popup-content">
        <p>商品不存在</p>
    </div>
@endsection
@push('scripts')
<script>
        const ratings = [];
        var ratingLength =  document.querySelectorAll('.rating-box').length;
        document.querySelectorAll('.rating-box').forEach(ratingBox => {
            const index = ratingBox.dataset.index; 
            const stars = ratingBox.children;
            let ratingValue = 0;
            let activeIndex = -1; 

            for (let i = 0; i < stars.length; i++) {
                stars[i].addEventListener("mouseover", function () {
                    for (let j = 0; j < stars.length; j++) {
                        stars[j].classList.remove("full");
                    }
                    for (let j = 0; j <= i; j++) {
                        stars[j].classList.add("full");
                    }
                });

                stars[i].addEventListener("click", function () {
                    ratingValue = i + 1;
                    activeIndex = i;
                    ratings[index] = ratingValue;
                    document.getElementById(index).value = ratingValue;
                    var ratingCount = 0;
                    if(ratings.length==ratingLength){
                        for (let i = 0; i < ratingLength; i++) {
                            if(0<ratings[i] && ratings[i]<=5){
                                ratingCount++;
                                console.log(ratingCount);
                            }
                        }
                    console.log(ratings[0]);
                    if(ratingCount==ratingLength){
                        $('.btn-prohibit').hide();
                        $('.btn-accent').show(); 
                    }
                }
                });

                stars[i].addEventListener("mouseout", function () {
                    for (let j = 0; j < stars.length; j++) {
                        stars[j].classList.remove("full");
                    }
                    for (let j = 0; j <= activeIndex; j++) {
                        stars[j].classList.add("full");
                    }
                });
            }
        });
    </script>
    <script>
        function act1() { 
            $('#hidden').show();
        } 
        function act2() { 
            $('#hidden').hide();
        }
        async function act3(id) { 
            try {
                const response = await fetch(`/user/order/${id}/repurchase`);
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
                const data = await response.json();

                if (data.productExists) {
                    window.location.href = `/user/cart`;
                } else {
                    // 如果商品不存在，顯示提示訊息
                    $('.hidden').show();
                    setTimeout(function() {
                        $('.hidden').hide(); 
                    }, 3000);
                }
            } catch (error) {
                console.error('Error occurred:', error);
                alert('發生錯誤，請稍後再試');
            }
        } 
    </script>
@endpush