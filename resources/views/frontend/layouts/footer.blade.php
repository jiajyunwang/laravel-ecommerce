<script src="{{asset('build/assets/app-BPhBxfOi.js')}}"></script>
<script src="{{asset('frontend/js/jquery-3.7.1.min.js')}}"></script>
<script src="{{asset('frontend/js/demo.js')}}"></script>
@auth
    @if (Auth::user()->role === 'user')
        <script type="module" src="{{asset('frontend/js/chat/user-chat.js')}}"></script>
    @elseif (Auth::user()->role === 'admin')
        <script type="module" src="{{asset('frontend/js/chat/admin-chat.js')}}"></script>
    @endif
@endauth
@stack('scripts')