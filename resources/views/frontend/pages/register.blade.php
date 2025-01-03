@extends('frontend.layouts.master')
@section('main-content')
    <div class="title">
        <p>註冊</p>
    </div>
    <div class="form">
        <form method="post" action="{{route('register.submit')}}">
            @csrf    
            <label>暱稱<span>*</span></label>
            <input type="text" name="nickname" value="{{old('nickname')}}" required="required">
            @error('nickname')
                <span class="error">{{$message}}</span>
            @enderror

            <label>Email<span>*</span></label>
            <input type="text" name="email" value="{{old('email')}}" required="required">
            @error('email')
                <span class="error">{{$message}}</span>
            @enderror

            <label>密碼<span>*</span></label>
            <input type="password" name="password" required="required">
            @error('password')
                <span class="error">{{$message}}</span>
            @enderror

            <label>再次輸入密碼<span>*</span></label>
            <input type="password" name="password_confirmation" required="required">
            @error('password_confirmation')
                <span class="error">{{$message}}</span>
            @enderror

            <button type="submit">註冊</button>
        </form>
    </div>
@endsection




