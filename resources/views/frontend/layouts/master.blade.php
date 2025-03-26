<!DOCTYPE html>
<html lang="zh-Hant-TW">
<head>
    <meta name="csrf-token" content="{{csrf_token()}}">
    @if (session('token')!== null)
        <meta name="access-token" content="{{session('token')}}">
	@endif
    @include('frontend.layouts.head')	
</head>
<body class="bg-primary">

    @include('frontend.layouts.header')
    
    @yield('main-content')

    @include('frontend.layouts.footer')

    @include('frontend.layouts.chat')
    
</body>
</html>