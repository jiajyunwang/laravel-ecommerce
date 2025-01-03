@extends('frontend.layouts.master')
@section('main-content')
    <p class="title">我的帳戶</p>
    <form class="form" id="accountForm" method="post" action="{{route('account.submit')}}">
        @csrf 
        <div class="user-email">
            <label>Email</label>&emsp;<p class="">{{Auth::user()->email}}</p>
        </div>
        <label>暱稱<span>*</span></label>
        <input type="text" name="nickname" value="{{Auth::user()->nickname}}">
        @error('nickname')
            <span class="error">{{$message}}</span>
        @enderror

        <label>姓名</label>
        <input type="text" name="name" value="{{Auth::user()->name}}">
        @error('name')
            <span class="error">{{$message}}</span>
        @enderror

        <label>手機號碼</label>
        <input type="tel" name="cellphone" value="{{Auth::user()->cellphone}}">
        @error('cellphone')
            <span class="error">{{$message}}</span>
        @enderror

        <label>地址</label>
        <input type="text" name="address" value="{{Auth::user()->address}}">
        @error('address')
            <span class="error">{{$message}}</span>
        @enderror

        <button type="submit">儲存</button>
    </form>
    <div id="overlay">
        <div class="popup">
            <div class="success"><i class="ti-check"></i></div>
            <p>檔案已更新</p>
        </div>
    </div>
@endsection