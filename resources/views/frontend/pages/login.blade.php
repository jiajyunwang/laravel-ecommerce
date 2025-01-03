@extends('frontend.layouts.master')
@section('main-content')
    <div class="title">
        <p>登入</p>
    </div>
    <div class="form">
        <form method="post" action="{{route('login.submit')}}">
            @csrf
            <label>Email</label>
            <input type="text" name="email" required="required" value="{{old('email')}}">
            @error('email')
                <span class="error">{{$message}}</span>
            @enderror
            @if (session('error'))
                <div class="error">
                    {{ session('error') }}
                </div>
            @endif

            <label>密碼</label>
            <input type="password" name="password" required="required"><br>
            @error('password')
                <span class="error">{{$message}}</span>
            @enderror

            <button type="submit">登入</button>
        </form>
    </div>
@endsection