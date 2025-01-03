<!DOCTYPE html>
<html lang="zh-Hant-TW">
<head>
    @include('backend.layouts.head')	
</head>
<body class="bg-primary">
    
    @include('backend.layouts.header')

    @yield('main-content')
    
    @include('backend.layouts.footer')

</body>
</html>