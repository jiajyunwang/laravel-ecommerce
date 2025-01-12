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
                <form method='POST' id="myForm" action="{{route('request.action', [$product->slug])}}">
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
                    <button id="btn1" data-product-id="{{$product->id}}" data-sort="created_at" data-order="desc" class="btn active sort-button" type="button">最新</button>
                    <button id="btn2" data-sort="rate" data-order="desc" class="btn sort-button" type="button">最高評分</button>
                    <button id="btn3" data-sort="rate" data-order="asc" class="btn sort-button" type="button">最低評分</button>
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
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let page = 1;
            let sortBy = 'created_at';
            let sortOrder = 'desc';
            const reviewContainer = document.getElementById('review-container');
            const loadingIndicator = document.getElementById('loading-indicator');
            const btn1 = document.getElementById('btn1');
            const productId = btn1.dataset.productId;

            let observer = new IntersectionObserver(loadMoreReviews);

            document.querySelectorAll('.sort-button').forEach((button) => {
                button.addEventListener('click', function () {
                    var element = document.querySelector('.nav-tabs').children;
                    for (let i = 0; i < element.length; i++) {
                        element[i].classList.remove("active");
                    }
                    button.classList.add("active");
                    sortBy = this.dataset.sort; 
                    sortOrder = this.dataset.order;
                    page = 0;
                    reviewContainer.innerHTML = ''; 
                    observer.disconnect(); 
                    observer.observe(loadingIndicator);
                });
            });

            function loadMoreReviews(entries) {
                console.log(entries);
                if (entries[0].isIntersecting) {
                    loadingIndicator.textContent = '';
                    loadingIndicator.classList.add("loader");
                    page++;
                    fetchReviews(page, sortBy, sortOrder, productId);
                }
            }

            observer.observe(loadingIndicator);

            async function fetchReviews(page, sortBy, sortOrder, productId) {
                try {
                    const response = await fetch(
                        `/reviews/fetch?page=${page}&sort_by=${sortBy}&sort_order=${sortOrder}&product_id=${productId}`
                    );
                    if (!response.ok) throw new Error('Network response was not ok');
                    const data = await response.json();
                    loadingIndicator.classList.remove("loader");
                    
                    if (data.data.length === 0) {
                        observer.disconnect(); 
                        loadingIndicator.textContent = '無更多評價';
                        return;
                    }
                    
                    data.data.forEach((review) => {
                        const reviewElement = createReviewElement(review);
                        reviewContainer.appendChild(reviewElement);
                    });

                    observer.disconnect(); 
                    observer.observe(loadingIndicator);
                } catch (error) {
                    console.error('Error fetching reviews:', error);
                }
            }
            function createReviewElement(review) {
                const div = document.createElement('div');
                const date = new Date(review.created_at);
                const formattedDate = date.toISOString().split('T')[0];
                div.innerHTML = `
                    <div class="review-inner m-b-m">
                        <p class="m-0">${review.users.name}</p>
                        <div class="ratings">
                            <div class="empty-stars"></div>
                            <div class="full-stars" style="width:${review.percentage}%"></div>
                        </div>
                        <p class="m-b-l">${review.review}</p>
                        <p class="date">${formattedDate}</p>
                    </div>
                `;
                return div;
            }
        });
    </script>
	<script>
        var stock = document.getElementById('product').dataset.stock;
        var quantity = document.getElementById('product').dataset.quantity;
        $(function() {
            $('.qtyplus').click(function(e) {
                $('#understock').hide();
                $('#sold-out').hide();
                e.preventDefault();
                fieldName = $(this).attr('field');
                var currentVal = parseInt($('input[name=' + fieldName + ']').val());
                if (!isNaN(currentVal) && currentVal < stock) {
                    $('input[name=' + fieldName + ']').val(currentVal + 1);
                    $('#upper-limit').hide(); 
                    if (currentVal >= (stock-quantity)) {
                        $('input[name=' + fieldName + ']').val(stock-quantity);
                        $('#upper-limit').show(); 
                    }
                } else if (!isNaN(currentVal) && currentVal > stock) {
                    $('input[name=' + fieldName + ']').val(stock);
                    if (currentVal > (stock-quantity)) {
                        $('input[name=' + fieldName + ']').val(stock-quantity);
                    }
                    $('#upper-limit').show(); 
                } 
            });
            $(".qtyminus").click(function(e) {
                $('#understock').hide();
                $('#sold-out').hide();
                e.preventDefault();
                fieldName = $(this).attr('field');
                var currentVal = parseInt($('input[name=' + fieldName + ']').val());
                if (!isNaN(currentVal) && currentVal > 1) {
                    $('input[name=' + fieldName + ']').val(currentVal - 1);
                    $('#upper-limit').hide(); 
                } else {
                    $('input[name=' + fieldName + ']').val(1);
                    $('#upper-limit').hide(); 
                }
            });
            $('input[name="quantity"]').on('input', function() {
                $('#understock').hide();
                $('#sold-out').hide();
                var currentVal = parseInt($(this).val());
                if (!isNaN(currentVal) && currentVal > stock) {
                    $(this).val(stock);
                    if (currentVal >= (stock-quantity)) {
                        $(this).val(stock-quantity);
                    }
                    $('#upper-limit').show(); 
                } else if (currentVal == stock) {
                    if (currentVal > (stock-quantity)) {
                        $(this).val(stock-quantity);
                    }
                    $('#upper-limit').hide(); 
                } else if (currentVal > 0 && currentVal < stock) {
                    $('#upper-limit').hide(); 
                    if (currentVal > (stock-quantity)) {
                        $(this).val(stock-quantity);
                        $('#upper-limit').show(); 
                    }
                } else if (currentVal < 1) {
                    $('#upper-limit').hide(); 
                    $(this).val(1);
                }
            });
            $('input[name="quantity"]').blur(function() {
                var currentVal = parseInt($(this).val());
                if (isNaN(currentVal)) {
                    $('#upper-limit').hide();
                    $(this).val(1);
                }
            });
        });
	</script>
	<script>
        $(document).ready(function() {
            $('#cart').on('click', function(event) {
                $('#understock').hide();
                $('#sold-out').hide();
                $('#upper-limit').hide(); 
                event.preventDefault(); 
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: $('#myForm').attr('action'),
                    method: $('#myForm').attr('method'),
                    data: $('#myForm').serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('.count').text(response.count);
                            $('#hidden').show();
                            setTimeout(function() {
                                $('#hidden').hide(); 
                            }, 3000);
                        } else if (response.notEnough) {
                            $('#understock').show(); 
                        } else if (response.finish) {
                            $('#sold-out').show();
                        } else if (response.notLongin){
                            window.location.href = '/user/login';
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log(jqXHR);
                        alert('提交失敗，請重試。');
                    }
                });
            });
        });
	</script>
@endpush