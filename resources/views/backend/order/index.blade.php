@extends('backend.layouts.master')
@section('main-content')
    <div class="card">
        <form class="form-product" method="POST" name="form" action="{{route('order.search')}}">
            @csrf
            <div class="card-header bg-light">
                <div class="order-search">
                    <input name="orderNumber" placeholder="訂單編號">
                    <button type="submit">訂單查詢</button>
                    @if (session('error'))
                        <div class="popup-content">
                            <p>{{ session('error') }}</p>
                        </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table class="table table-product" id="product-dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>訂單編號</th>
                            <th>收件人</th>
                            <th>付款方式</th>
                            <th>金額</th>
                            <th>狀態</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td class="text-center nowrap">{{$order->order_number}}</td>
                                <td class="text-center nowrap">{{$order->name}}</td>
                                <td class="text-center nowrap">貨到付款</td>
                                <td class="text-center nowrap">${{$order->total_amount}}</td>
                                @if($type=='unhandled')
                                    <td class="text-center nowrap text-danger">待出貨</td>
                                @elseif($type=='shipping')
                                    <td class="text-center nowrap text-primary">待收貨</td>
                                @elseif($type=='completed')
                                    <td class="text-center nowrap text-success">完成</td>
                                @elseif($type=='cancel')
                                    <td class="text-center nowrap">取消</td>
                                @endif
                                <td class="text-center">
                                    <a target="_blank" class="operation nowrap" href="{{route('admin.order.detail', [$order->id])}}">下載</a>
                                    @if($type=='unhandled')
                                        <a class="operation nowrap" href="{{route('to-shipping', [$order->id])}}">出貨</a>
                                        <a class="operation nowrap" href="{{route('admin.to-cancel', [$order->id])}}">取消</a>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <div class="text-center flip m-b-m">
                                        <p class="m-0">訂單明細🔻</p>
                                    </div>
                                    <div class="panel">
                                        @foreach($order['order_details'] as $detail)
                                            <div>
                                                <p class="order-detail">{{$detail->title}}</p>   
                                                <p class="order-detail">x{{$detail->quantity}}</p>   
                                                <p class="order-detail">${{$detail->amount}}</p>   
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="panel">    
                                        備註:<p class="text-danger"> {{$order->note}}</p> 
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="center">{{ $orders->links() }}</div>
            </div>  
        </form>
    </div>
@endsection
@push('scripts')       
    <script> 
        $(".flip").click(function() {
            if ($(".panel").is(":visible")) {
                $(this).text("訂單明細🔻");
            }else {
                $(this).text("訂單明細🔺");
            }
            var panel = $(this).closest("td").find(".panel");
            panel.slideToggle("slow");
        });
    </script>
	<script>
        $(".checkAll").click(function(){
            if($(this).prop("checked")){
                $("input[type='checkbox']").prop("checked",true);
            }else{
                $("input[type='checkbox']").prop("checked",false);
            }
        })
        $("input[type='checkbox']").click(function(){
            var checkLength = $(this).closest("tbody").find("input[type='checkbox']:checked").length;
            var inputLenhth = $(this).closest("tbody").find("input[type='checkbox']").length;

            if(!$(this).prop("checked")){
                $(".checkAll").prop("checked",false);
                $(".checkAll").prop("checked",false);
            }
            else{
                if(checkLength==inputLenhth){
                    $(".checkAll").prop("checked",true);
                    $(".checkAll").prop("checked",true);
                }
            }
        })
	</script>
    <script>
        $(document).ready(function() {
            setTimeout(function() {
                $('.popup-content').fadeOut('slow');
            }, 3000); // 提示框3秒後消失
        });
    </script>
@endpush