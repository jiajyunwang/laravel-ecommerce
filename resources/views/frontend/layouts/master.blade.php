<!DOCTYPE html>
<html lang="zh-Hant-TW">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
	@include('frontend.layouts.head')	
</head>
<body class="bg-primary">

    @include('frontend.layouts.header')
    
    @yield('main-content')

    @include('frontend.layouts.footer')

    @include('frontend.layouts.chat')
    
</body>
</html>