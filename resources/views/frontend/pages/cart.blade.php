@extends('frontend.layouts.master')
@section('main-content')
    <div class="title">
        <p>購物車</p>
    </div>
    <div class="content">
        <form name="form" method="POST" action="">
            @csrf
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
                    <button onClick="act1();">刪除</button>
                </div>
            </div>
                @if(Helper::getAllProductFromCart()->isNotEmpty())
                    <div>
                        <button class="btn right btn-dark" onClick="act2();">去結帳</button>
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
@push('scripts')
	<script>
        $(function() {
            $('.qtyplus').click(function(e) {
                e.preventDefault();
                fieldName = $(this).attr('field');
                var currentVal = parseInt($('input[name=' + fieldName + ']').val());
                var stock = $('input[name=' + fieldName + ']').data('stock');
                var quantityVal = $('input[name=' + fieldName + ']').data('quantity');
                var price = $('input[name=' + fieldName + ']').data('price');
                var amountVal = quantityVal*price;
                if (!isNaN(currentVal) && currentVal < stock) {
                    $('input[name=' + fieldName + ']').val(++currentVal);
                    $('#' + fieldName ).text('$' + (currentVal*price));
                    amountVal = currentVal*price;
                } else if (!isNaN(currentVal) && currentVal > stock) {
                    $('input[name=' + fieldName + ']').val(stock);
                    $('#' + fieldName ).text('$' + (stock*price));
                    currentVal = stock;
                    amountVal = stock*price;
                }
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: "http://127.0.0.1:8000/cart-update",
                    method: "post",
                    data: {
                        product_id: fieldName,
                        quantity: currentVal,
                        amount: amountVal
                    },
                    dataType: "json",
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('提交失敗，請重試。1');
                    }
                });
            });
            $(".qtyminus").click(function(e) {
                e.preventDefault();
                fieldName = $(this).attr('field');
                var currentVal = parseInt($('input[name=' + fieldName + ']').val());
                var stock = $('input[name=' + fieldName + ']').data('stock');
                var quantityVal = $('input[name=' + fieldName + ']').data('quantity');
                var price = $('input[name=' + fieldName + ']').data('price');
                var amountVal = quantityVal*price;
                if (!isNaN(currentVal) && currentVal > 1) {
                    $('input[name=' + fieldName + ']').val(--currentVal);
                    $('#' + fieldName ).text('$' + (currentVal*price));
                    amountVal = currentVal*price;
                } else {
                    $('input[name=' + fieldName + ']').val(1);
                    $('#' + fieldName ).text('$' + (1*price));
                    currentVal = 1;
                    amountVal = 1*price;
                }
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: "http://127.0.0.1:8000/cart-update",
                    method: "post",
                    data: {
                        product_id: fieldName,
                        quantity: currentVal,
                        amount: amountVal
                    },
                    dataType: "json",
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('提交失敗，請重試。2');
                    }
                });
            });
            $(".qty").blur(function() {
                fieldName = $(this).attr('field');
                var currentVal = parseInt($(this).val());
                var stock = $('input[name=' + fieldName + ']').data('stock');
                var quantityVal = $('input[name=' + fieldName + ']').data('quantity');
                var price = $('input[name=' + fieldName + ']').data('price');
                var amountVal = quantityVal*price;
                if (!isNaN(currentVal) && currentVal > stock) {
                    $('input[name=' + fieldName + ']').val(stock);
                    $('#' + fieldName ).text('$' + (stock*price));
                    currentVal = stock;
                    amountVal = stock*price;
                }
                else if (currentVal >= 1  && currentVal <= stock) {
                    $('#' + fieldName ).text('$' + (currentVal*price));
                    amountVal = currentVal*price;
                }
                else if (currentVal < 1) {
                    $('input[name=' + fieldName + ']').val(1);
                    $('#' + fieldName ).text('$' + (1*price));
                    currentVal = 1;
                    amountVal = 1*price;
                }
                else if (isNaN(currentVal)) {
                    $('input[name=' + fieldName + ']').val(1);
                    $('#' + fieldName ).text('$' + (1*price));
                    currentVal = 1;
                    amountVal = 1*price;
                }
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: "http://127.0.0.1:8000/cart-update",
                    method: "post",
                    data: {
                        product_id: fieldName,
                        quantity: currentVal,
                        amount: amountVal
                    },
                    dataType: "json",
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('提交失敗，請重試。3');
                    }
                });
            });
        });
	</script>
	<script>
        $(".checkAll").click(function(){
            if($(this).prop("checked")){
                $("input[type='checkbox']").prop("checked",true);
                $('.btn-prohibit').hide();
                $('.btn-dark').show(); 
            }else{
                $("input[type='checkbox']").prop("checked",false);
                $('.btn-prohibit').show();
                $('.btn-dark').hide(); 
            }
        })
        $(".checkAll2").click(function(){
            if($(this).prop("checked")){
                $("input[type='checkbox']").prop("checked",true);
                $('.btn-prohibit').hide();
                $('.btn-dark').show(); 
            }else{
                $("input[type='checkbox']").prop("checked",false);
                $('.btn-prohibit').show();
                $('.btn-dark').hide(); 
            }
        })
        $("input[type='checkbox']").click(function(){
            var checkLength = $(this).closest("tbody").find("input[type='checkbox']:checked").length;
            var inputLenhth = $(this).closest("tbody").find("input[type='checkbox']").length;

            if(!$(this).prop("checked")){
                $(".checkAll").prop("checked",false);
                $(".checkAll2").prop("checked",false);
                if(checkLength==0){
                    $('.btn-prohibit').show();
                    $('.btn-dark').hide(); 
                }
            }
            else{
                if(checkLength==inputLenhth){
                    $(".checkAll").prop("checked",true);
                    $(".checkAll2").prop("checked",true);
                }
                $('.btn-prohibit').hide();
                $('.btn-dark').show(); 
            }
        })
	</script>
    <script> 
        function act1() 
        { 
            document.form.action="{{route('destroy.carts')}}"; 
            document.form.submit();
        } 
        function act2() 
        { 
            document.form.action="{{route('order.create')}}"; 
            document.form.submit();
        } 
    </script>
@endpush