@extends('frontend.layouts.master')
@section('main-content')
@include('frontend.layouts.order_status')
    <div id="order-container" data-type="{{$type}}">
        @foreach($orders as $order)
            <div id="hidden" class="popup-bg order-{{$order->id}}">
                <div class="review-popup">
                    <form id="form-{{$order->id}}" name="form" method="POST" action="{{route('review')}}">
                        @csrf
                        <input type="hidden" name="order_id" value="{{$order->id}}">
                        @php
                            $index = 0;
                        @endphp
                        @foreach($order['order_details'] as $detail)
                            <div class="review">
                                <p>{{$detail->title}}</p>
                                <div class="rating-box rating-{{$order->id}}" data-index="{{$index}}">
                                    <span id="star-1" class="empty-stars" field="{{$order->id}}"></span>
                                    <span id="star-2" class="empty-stars" field="{{$order->id}}"></span>
                                    <span id="star-3" class="empty-stars" field="{{$order->id}}"></span>
                                    <span id="star-4" class="empty-stars" field="{{$order->id}}"></span>
                                    <span id="star-5" class="empty-stars" field="{{$order->id}}"></span>
                                </div>
                                <div class="review-inner">
                                    <textarea class="comment" name="review[]"></textarea>
                                </div>
                                <input type="hidden" id="rate-{{$order->id}}-{{$index++}}" name="rate[]">
                            </div>
                        @endforeach
                    </form>
                    <div class="button">
                        <button class="btn right btn-accent hide btn-accent-{{$order->id}}" form="form-{{$order->id}}">送出</button>
                        <button class="btn right btn-prohibit btn-prohibit-{{$order->id}}" type="button">送出</button>
                        <button class="btn right btn-dark" type="button" field="{{$order->id}}">取消</button>
                    </div>
                </div>
            </div>
            <div id="order-container" class="content">
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
                <a href="{{route('order.detail', [$order->id])}}">
                    <table class="table table-cart">   
                        <thead>
                            <tr>
                            <th>商品</th>
                            <th>數量</th>
                            <th>金額</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->order_details as $orderDetail)
                                <tr>  
                                    <td><p class="product-title">{{$orderDetail->title}}</p></td>
                                    <td><p class="text-center">x{{$orderDetail->quantity}}</p></td>
                                    <td><p class="text-center">${{$orderDetail->amount}}</p></td>
                                </tr>
                            @endforeach
                        </tbody>      
                    </table>
                </a>
                <div class="amount">
                    <label class="m-r-m">訂單合計:</label> 
                    <p class="text-danger">${{$order->total_amount}}</p>
                </div>
                <div class="center">
                    @if($type=='unhandled')
                        <form method="GET" action="{{route('to-cancel', $order->id)}}">
                            @csrf
                            <button class="btn m-t-m btn-dark">取消訂單</button>
                        </form>
                    @elseif($type=='shipping')
                        <form method="GET" action="{{route('to-completed', $order->id)}}">
                            @csrf
                            <button class="btn m-t-m m-r-m btn-accent">完成訂單</button>
                        </form>
                    @elseif($type=='completed')
                        <button class="btn m-t-m m-r-m btn-accent btn-review" onClick="act1({{$order->id}});">評價</button>
                        <button class="btn m-t-m m-r-m btn-dark" onClick="act2({{$order->id}});">重新購買</button>
                    @elseif($type=='cancel')
                        <button class="btn m-t-m btn-dark" onClick="act2({{$order->id}});">重新購買</button>
                    @endif
                </div>
            </div>
            <div id="loading-indicator" class=""></div>
            <div class="hidden popup-content">
                <p>商品不存在</p>
            </div>
        @endforeach
    </div>
    <div class="center grid-colum-2">
        <div id="loading"></div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let page = 1;
            let sortBy = 'created_at';
            let sortOrder = 'desc';
            const orderContainer = document.getElementById('order-container');
            const loading = document.getElementById('loading');
            const orderType = document.getElementById('order-container').dataset.type;

            let observer = new IntersectionObserver(loadMoreOrders); 

            function loadMoreOrders(entries) {
                if (entries[0].isIntersecting) {
                    loading.textContent = '';
                    loading.classList.add("loader");
                    page++;
                    fetchOrders(page, sortBy, sortOrder, orderType);
                }
            }

            observer.observe(loading);

            async function fetchOrders(page, sortBy, sortOrder, orderType) {
                try {
                    const response = await fetch(
                        `/user/orders/fetch?page=${page}&type=${orderType}`
                    );
                    if (!response.ok) throw new Error('Network response was not ok');
                    const html = await response.text();
                    loading.classList.remove("loader");
                    
                    if (html.trim().length === 0) {
                        observer.disconnect();
                        loading.textContent = '無更多訂單';
                        return;
                    }
                    
                    const container = document.getElementById('order-container');
                    container.insertAdjacentHTML('beforeend', html);
                    
                    if (isElementInViewport(loading)) {
                        loadMoreOrders([{ isIntersecting: true }]); // 手動觸發觀察器邏輯
                    }
                } catch (error) {
                    console.error('Error fetching orders:', error);
                }
            }
            function isElementInViewport(el) {
                const rect = el.getBoundingClientRect();
                return (
                    rect.top >= 0 &&
                    rect.left >= 0 &&
                    rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                    rect.right <= (window.innerWidth || document.documentElement.clientWidth)
                );
            }
        });
    </script>
    <script>
        var ratings = [];
        $(document).on('click', '.btn-review', function() {
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

                    stars[i].addEventListener("click", function (e) {
                        ratingValue = i + 1;
                        activeIndex = i;
                        ratings[index] = ratingValue;
                        const fieldName = $(this).attr('field');
                        document.getElementById('rate-'+fieldName+'-'+index).value = ratingValue;
                        var ratingCount = 0;
                        var ratingLength =  document.querySelectorAll('.rating-' + fieldName).length;
                        if(ratings.length==ratingLength){
                            for (let i = 0; i < ratingLength; i++) {
                                if(0<ratings[i] && ratings[i]<=5){
                                    ratingCount++;
                                }
                            }
                            if(ratingCount==ratingLength){
                                $('.btn-prohibit-' + fieldName).hide();
                                $('.btn-accent-' + fieldName).show(); 
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
                $(document).on('mouseover', '.btn-dark', function() {
                    $('.btn-dark').click(function(e) {
                        const fieldName = $(this).attr('field');
                        $(`.order-`+fieldName).hide();
                        $(`.order-`+fieldName).find('textarea').val('');
                        $('.btn-prohibit-' + fieldName).show();
                        $('.btn-accent-' + fieldName).hide(); 
                        for (let j = 0; j < stars.length; j++) {
                            stars[j].classList.remove("full");
                        }
                        ratings = [];
                    });
                });
            });
        });
        function act1(id) { 
            $(`.order-${id}`).show();
        } 
    </script>
    <script>
        async function act2(id) { 
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