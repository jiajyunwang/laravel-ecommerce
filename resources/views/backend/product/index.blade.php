@extends('backend.layouts.master')
@section('main-content')
    <div class="card">
        <form class="form-product" method="POST" name="form" action="{{route('destroy.products')}}">
            @csrf
            <div class="card-header bg-light">
                <div class="left">
                    <div class="check-all">
                        <input type="checkbox" class="checkAll" />
                        <label>全選</label>
                    </div>&emsp;
                    <div class="btn-delete">
                        <button id="delete" type="button">刪除</button>
                    </div>
                </div>
                @if($type=='listed')
                    <div class="right">
                        <div class="btn-product-create right">
                            <a href="{{route('product.create')}}"><div><p><i class="ti-plus"></i> 新增商品</p></div></a>
                        </div>
                    </div>
                @endif
            </div>
            <div class="card-body">
                <table class="table table-product" id="product-dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>商品</th>
                            <th>價錢</th>
                            <th>庫存</th>
                            <th>圖片</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>商品</th>
                            <th>價錢</th>
                            <th>庫存</th>
                            <th>圖片</th>
                            <th>操作</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>
                                    <div class="product-title">
                                        <input type="checkbox" name="check[]" value="{{$product->id}}">
                                        <a href="{{route('product-detail', [$product->id])}}">{{$product->title}}</a>
                                    </div>
                                </td>
                                <td class="text-center"><p>{{$product->price}}</p></td>
                                <td class="text-center"><p>{{$product->stock}}</p></td>
                                <td class="text-center">
                                    <img src="{{asset($product->photo)}}" style="max-width:80px">
                                </td>
                                <td>
                                    <button title="編輯" class="btn-edit" data-product-id="{{$product->id}}"><i class="ti-pencil-alt"></i></button>
                                    @if($type=='listed')
                                        <button title="下架" class="to-inactive" data-product-id="{{$product->id}}"><i class="ti-import"></i></button>
                                    @elseif($type=='unlisted')
                                        <button title="上架" class="to-active" data-product-id="{{$product->id}}"><i class="ti-export"></i></button>
                                    @endif
                                    <button id="single-delete" title="刪除" class="btn-delete" data-product-id="{{$product->id}}"><i class="ti-trash"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>    
    </div>
@endsection