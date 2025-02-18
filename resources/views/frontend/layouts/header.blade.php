<div class="header-inner">
    <div class="items left">
        <a href="{{route('home')}}">首頁</a>
    </div>
    <div class="items right">
    @auth
        @php
            $count = count(Helper::getAllProductFromCart());
        @endphp
        <a href="{{route('cart')}}">
            <i class="ti-shopping-cart-full"></i>
            <p class="text-transparent" >購物車(<span class="count">{{$count}}</span>)</p>
        </a>&emsp;
        <ul class="dropdown">    
            <li><a>{{Auth::user()->email}}</a>
                <ul>
                    <li><a href="{{route('account.form')}}">我的帳戶</a></li>
                    <li><a href="{{route('user.order')}}">訂單查詢</a></li>
                    <li><a href="{{route('logout')}}">登出</a></li>
                </ul>
            </li>
        </ul>
    @else
        <a href="{{route('login.form')}}">登入</a> 
        <nobr>︱</nobr>
        <a href="{{route('register.form')}}">註冊</a>
    @endauth
    </div>
</div>
