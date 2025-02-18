@extends('frontend.layouts.master')
@section('main-content')
    <div class="search-bar">
        <input name="search" placeholder="搜尋商品" type="search">
        <button type="submit"><i class="ti-search"></i></button>
    </div>
   
    <div class="product-briefing">
        <div class="product-img">
            <img src="{{asset($product->photo)}}">
        </div>
        <div class="product-info">
            <div class="product-title">
                <p>{{$product->title}}</p>
                <div class="rate-average">
                    <p class="m-0">{{$average}}</p>
                    <div class="ratings">
                        <div class="empty-stars"></div>
                        <div class="full-stars" style="width:{{$percentage}}%"></div>
                    </div>
                    <p class="text-center">({{$reviewCount}})</p>
                </div>
            </div>
            <div class="price">
                <p>${{$product->price}}</p>
            </div>
            <div class="delivery">
                <i class="ti-truck"></i><p>宅配${{$homeDeliveryFee}}</p>
            </div>
            <div class="stock">
                <form method='POST' id="myForm" action="{{route('request.action', [$product->id])}}">
                    @csrf
                    <label>購買數量 </label>
                    <div class="form-group">
                        <div class="minus">
                            <i class="ti-minus"></i>
                            <input type='button' class='qtyminus' field='quantity'>
                        </div>
                        @php
                            use App\Models\Cart;
                        @endphp
                        @auth
                            @php
                                $already_cart = Cart::where('user_id', Auth::user()->id)->where('product_id', $product->id)->first();
                            @endphp
                            @if(isset($already_cart))
                                <div id="product" data-stock="{{$product->stock}}" data-quantity="{{$already_cart->quantity}}">
                            @else
                                <div id="product" data-stock="{{$product->stock}}" data-quantity='0'>
                            @endif
                        @else
                            <div id="product" data-stock="{{$product->stock}}" data-quantity='0'>
                        @endauth
                            <input type='text' name='quantity' value='1' class='qty' oninput="value=value.replace(/[^\d]/g,'')">
                        </div>
                        <div class="plus">
                            <i class="ti-plus"></i>
                            <input type='button' class='qtyplus' field='quantity'>
                        </div>
                        <p>(庫存{{$product->stock}}件)</p>
                        <span id="understock">庫存不足，請重新選取數量。</span>
                        <span id="sold-out">已售完</span>
                        <span id="upper-limit">已達購買上限</span>
                    </div>   
                </form> 
            </div>
            <div class="checkout">
                <button type="submit" form="myForm" name="requestAction" value="checkout">直接購買</button>
            </div>
            <div class="add-to-cart">
                <button id="cart" type="submit" name="requestAction" value="cart"><i class="ti-shopping-cart"></i>&thinsp;加入購物車</button>
            </div>
        </div>
    </div>
    <div class="product-description">
        <div class="card-header">
            <label>商品詳情</label>
        </div>
        <div class="description-body">
            {!! html_entity_decode($product->description) !!}
        </div>
    </div>
    <div class="product-review">
        <div class="card-header">
            <label>評價</label>
        </div>
        <div class="description-body">
            <div class="nav m-b-m">
                <div class="rate-average">
                    <p class="fs-l fw m-0 text-center">{{$average}}</p>
                    <div class="ratings">
                        <div class="empty-stars"></div>
                        <div class="full-stars" style="width:{{$percentage}}%"></div>
                    </div>
                    <p class="text-center">({{$reviewCount}})</p>
                </div>
                <div class="nav-tabs">
                    <button id="newest-btn" data-product-id="{{$product->id}}" data-sort="created_at" data-order="desc" class="btn active sort-button" type="button">最新</button>
                    <button data-sort="rate" data-order="desc" class="btn sort-button" type="button">最高評分</button>
                    <button data-sort="rate" data-order="asc" class="btn sort-button" type="button">最低評分</button>
                </div>
            </div>
            <div id="review-container">
                @foreach($reviews as $review)
                    <div class="review-inner m-b-m">
                        <p class="m-0">{{$review['users']->nickname}}</p>
                        <div class="ratings">
                            <div class="empty-stars"></div>
                            <div class="full-stars" style="width:{{$review->percentage}}%"></div>
                        </div>
                        <p class="m-b-l">{{$review->review}}</p>
                        <p class="date">{{$review->created_at}}</p>
                    </div>
                @endforeach
            </div>
            <div id="loading-indicator" class=""></div>
        </div>
    </div>
    <div id="hidden" class="popup-content">
        <p>已成功加入購物車</p>
    </div>
@endsection
