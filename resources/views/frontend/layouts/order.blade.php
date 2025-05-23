@foreach($orders as $order)
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
                    <button class="btn m-t-m m-r-s m-l-s btn-dark">取消訂單</button>
                </form>
            @elseif($type=='shipping')
                <form method="GET" action="{{route('to-completed', $order->id)}}">
                    @csrf
                    <button class="btn m-t-m m-r-s m-l-s btn-accent">完成訂單</button>
                </form>
            @elseif($type=='completed')
                @if(!$order->isReview)
                    <button class="btn m-t-m m-r-s m-l-s btn-accent btn-review" data-order-id="{{$order->id}}">評價</button>
                @endif
                <button id="again" class="btn m-t-m m-r-s m-l-s btn-dark" data-order-id="{{$order->id}}">重新購買</button>
            @elseif($type=='cancel')
                <button id="again" class="btn m-t-m btn-dark" data-order-id="{{$order->id}}">重新購買</button>
            @endif
        </div>
    </div>
@endforeach