@extends('frontend.layouts.master')
@section('main-content')
    <div class="search-bar">
        <input name="search" placeholder="搜尋商品" type="search">
        <button type="submit"><i class="ti-search"></i></button>
    </div>

    <div class="product-area">
        @if (isset($search))
            <div class="search-panel">
                <span id="search" data-search="{{$search}}">搜尋 '{{$search}}'</span>
                <div class="sort-by">
                    @if ($sortBy === '_score')
                        <span class="cursor active">最相關</span>
                    @else
                        <span class="cursor">最相關</span>
                    @endif
                    <span>|</span>
                    @if ($sortBy === 'price')
                        @if ($sortOrder === 'asc')
                            <span class="cursor active">價格🔺</span>
                        @elseif ($sortOrder === 'desc')
                            <span class="cursor active">價格🔻</span>
                        @endif
                    @else
                        <span class="cursor">價格🔺</span>
                    @endif
                </div>
            </div>
        @endif
        <div class="product-list">
            @foreach($products as $product)
                <div class="single-product">
                    <a href="{{route('product-detail',$product->id)}}">
                        <img class="product-img" src="{{asset($product->photo)}}">
                    </a>
                    <p class="product-title">{{$product->title}}</p>
                    <div class="rate-average">
                        <p class="m-0">{{$product->average}}</p>
                        <div class="ratings">
                            <div class="empty-stars"></div>
                            <div class="full-stars" style="width:{{$product->percentage}}%"></div>
                        </div>
                        <p class="text-center">({{$product->reviewCount}})</p>
                    </div>
                    <p class="product-price">${{$product->price}}</p>
                </div>
            @endforeach
        </div>
        <div class="bg-light center">{{ $products->links() }}</div>
    </div>
@endsection
@push('scripts')
    <script>
        let sortBy = null;
        let sortOrder = null;
        let search = null;
        let page = 0;
        $('.search-bar').find('button').click(function(){
            search = $('.search-bar').find('input').val();
            window.location.href = `/product/search?search=${search}`;
        })

        $('.sort-by').find('span').eq(0).click(function(){
            search = $('#search').data('search');
            window.location.href = `/product/search?search=${search}`;
        });

        $('.sort-by').find('span').eq(2).click(function(){
            search = $('#search').data('search');
            sortBy = 'price';
            if ($(this).text() === '價格🔺' && $(this).attr('class') === 'cursor active'){
                sortOrder = 'desc';
            } else {
                sortOrder = 'asc';
            }
            window.location.href = `/product/search?search=${search}&sortBy=${sortBy}&sortOrder=${sortOrder}`;
        });

        </script>
        <script>
            
    </script>
@endpush