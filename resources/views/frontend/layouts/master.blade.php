<!DOCTYPE html>
<html lang="zh-Hant-TW">
<head>
    <meta name="csrf-token" content="{{csrf_token()}}">
    <!-- <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests"> -->
    @if (session('token')!== null)
        <meta name="access-token" content="{{session('token')}}">
	@endif
    @include('frontend.layouts.head')
    <meta name="viewport" content="width=device-width, initial-scale=1">
	
</head>
<body class="bg-primary">

    @include('frontend.layouts.header')
    
    @yield('main-content')

    @include('frontend.layouts.footer')

    @include('frontend.layouts.chat')
    
</body>
</html>