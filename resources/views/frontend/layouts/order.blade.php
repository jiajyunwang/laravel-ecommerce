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
            </form>
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
                    <button class="btn m-t-m m-r-m btn-dark">取消訂單</button>
                </form>
            @elseif($type=='shipping')
                <form method="GET" action="{{route('to-completed', $order->id)}}">
                    @csrf
                    <button class="btn m-t-m m-r-m btn-accent">完成訂單</button>
                </form>
            @elseif($type=='completed')
                <button class="btn m-t-m m-r-m btn-accent btn-review" onClick="act1({{$order->id}});">評價</button>
            @endif
        </div>
    </div>
@endforeach