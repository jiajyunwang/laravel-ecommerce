@extends('backend.layouts.master')
@section('main-content')
    <div class="card">
        <div class="card-header">
            <div class="title">
                <p>新增商品</p>
            </div>
        </div>
        <div class="card-body">
            <form class="form" method="post" enctype="multipart/form-data" action="{{route('product.store')}}">
                @csrf
                <div class="form-group">
                    <label>商品標題<span>*</span></label>
                    <input class="form-control" type="text" name="title" required="required" value="{{old('title')}}">
                </div>

                <div class="form-group">
                    <label>價格<span>*</span></label>
                    <input class="form-control" type="number" min="1" name="price" required="required" value="{{old('price')}}">
                </div>

                <div class="form-group">
                    <label>庫存<span>*</span></label>
                    <input class="form-control" type="number" min="0" name="stock" required="required" value="{{old('stock')}}">
                </div>

                <div class="form-group">
                    <label>圖檔<span>*</span></label>
                    <input class="form-control" name="photo" type="file" data-target="preview_product_image" accept="image/*" required="required">
                    <img style="max-width: 400px" id="preview_product_image" src="{{asset('frontend/images/2024-07-09 214802.jpg')}}">
                </div>

                <div class="form-group">
                    <label>商品詳情<span>*</span></label>
                    <textarea id="editor" name="description">{{old('description')}}</textarea>
                    @error('description')
                        <p class="error">{{$message}}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <button class="create-button" type="submit">刊登</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
	<script>
        ClassicEditor
        .create(document.querySelector( 
            '#editor'))
        .then(editor=>{
            console.log(editor);
        })
        .catch(error=>{
            console.error(error);
        });
	</script>
	<script>
        var input = document.querySelector('input[name=photo]')
        input.addEventListener('change', function(e){
            readURL(e.target);   
        })
        function readURL(input){
            if(input.files && input.files[0]){
                var reader = new FileReader();
                reader.onload = function (e) {
                    var imgId = input.getAttribute('data-target')
                    var img = document.querySelector('#' + imgId)
                    img.setAttribute('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
	</script>
@endpush