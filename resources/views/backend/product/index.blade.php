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
                        <button  onClick="act1();">刪除</button>
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
                                        <a href="{{route('product-detail', [$product->slug])}}">{{$product->title}}</a>
                                    </div>
                                </td>
                                <td class="text-center"><p>{{$product->price}}</p></td>
                                <td class="text-center"><p>{{$product->stock}}</p></td>
                                <td class="text-center">
                                    <img src="{{asset($product->photo)}}" style="max-width:80px">
                                </td>
                                <td>
                                    <button title="編輯" class="btn-edit" onClick="act2({{$product->id}});"><i class="ti-pencil-alt"></i></button>
                                    @if($type=='listed')
                                        <button title="下架" class="to-inactive" onClick="act3({{$product->id}});"><i class="ti-import"></i></button>
                                    @elseif($type=='unlisted')
                                        <button title="上架" class="to-active" onClick="act5({{$product->id}});"><i class="ti-export"></i></button>
                                    @endif
                                    <button title="刪除" class="btn-delete" onClick="act4({{$product->id}});"><i class="ti-trash"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>    
    </div>
@endsection
@push('scripts')
    <script> 
        function act1() 
        { 
            document.form.action="{{route('destroy.products')}}"; 
            document.form.submit();
        } 
        function act2(id) 
        { 
            document.form.action="{{route('product.edit', ':id')}}".replace(':id', id); 
            document.form.submit();
        } 
        function act3(id) 
        { 
            document.form.action="{{route('to-inactive', ':id')}}".replace(':id', id); 
            document.form.submit();
        } 
        function act4(id) 
        { 
            document.form.action="{{route('product.destroy', ':id')}}".replace(':id', id); 
            document.form.submit();
        }
        function act5(id) 
        { 
            document.form.action="{{route('to-active', ':id')}}".replace(':id', id); 
            document.form.submit();
        } 
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
@endpush