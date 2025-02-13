<!DOCTYPE html>
<html lang="zh-Hant-TW">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
	@include('frontend.layouts.head')	
</head>
<body class="bg-primary">

    @include('frontend.layouts.header')
    
    @yield('main-content')

    @include('frontend.layouts.footer')

    @include('frontend.layouts.chat')
    
</body>
</html>