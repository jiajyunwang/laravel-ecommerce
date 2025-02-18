@extends('frontend.layouts.master')
@section('main-content')
@include('frontend.layouts.order_status')
    <div id="order-container" data-type="{{$type}}">
        @include('frontend.layouts.order')
        <div id="loading-indicator" class=""></div>
        <div class="hidden popup-content">
            <p>商品不存在</p>
        </div>
    </div>
    <div class="center grid-colum-2">
        <div id="loading"></div>
    </div>
@endsection