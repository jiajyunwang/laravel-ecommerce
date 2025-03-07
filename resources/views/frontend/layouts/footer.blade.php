@vite('resources/js/app.js')
<script src="{{asset('frontend/js/jquery-3.7.1.min.js')}}"></script>
<script src="{{asset('frontend/js/demo.js')}}"></script>
<script src="{{asset('frontend/js/cart.js')}}"></script>
<script src="{{asset('frontend/js/fetch-order.js')}}"></script>
<script src="{{asset('frontend/js/fetch-review.js')}}"></script>
<script src="{{asset('frontend/js/product-search.js')}}"></script>
<script src="{{asset('frontend/js/quantity.js')}}"></script>
<script src="{{asset('frontend/js/repurchase.js')}}"></script>
<script src="{{asset('frontend/js/review.js')}}"></script>
@auth
    @if (Auth::user()->role === 'user')
        <script type="module" src="{{asset('frontend/js/chat/user-chat.js')}}"></script>
    @elseif (Auth::user()->role === 'admin')
        <script type="module" src="{{asset('frontend/js/chat/admin-chat.js')}}"></script>
    @endif
@endauth
@stack('scripts')