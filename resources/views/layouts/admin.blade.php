<!DOCTYPE html>
<html lang="ja" class="no-js no-svg">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>梅子-Umeko-</title>
        <link href="{{ asset('css/admin/style.css') }}" rel="stylesheet">
    </head>
    <body>
        <div class="main">
            <div class="header">
                <div class="cms-name"><img src="{{ asset('/images/umeko-logo.png') }}" alt="梅子"></div>
                <div class="admin-bar">
                @guest
                @else
                    <div class="login-user">{{ Auth::user()->user_name }}でログイン中</div>
                    <div class="logout btn">
                        <a class="dropdown-item" href="{{ route('logout') }}"
                            onclick="event.preventDefault();
                                            document.getElementById('logout-form').submit();">
                            {{ __('Logout') }}
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                @endguest
                </div>
            </div>
            <div class="main-contents">
            @auth
                @include('layouts.admin-sidebar')
            @endauth
    @yield('content')
            </div>
        </div>
    <footer class="footer-area">
        <ul class="footer">
            <li><a href="{{ url('/2021/12/about') }}">梅子について</a></li>
            <li><a href="https://engineer-lady.com/about/">制作者プロフィール</a></li>
            <li><a href="https://github.com/naho-osada/umeko_cms">ソース（GitHub）</a></li>
        </ul>
        <div class="copyright">©&nbsp;2021-&nbsp;<a href="https://engineer-lady.com/about/">Naho&nbsp;Osada</a></div>
    </footer>
    @if(Request::is('admin/article/edit'))
        <script src="{{ asset('js/admin/trumbowyg.js') }}"></script>
        <script src="{{ asset('js/admin/article.js') }}"></script>
        <link rel="stylesheet" href="{{ asset('css/admin/trumbowyg.css') }}">
    @endif
    @if(Request::is('admin/html'))
        <script src="{{ asset('js/admin/html-maker.js') }}"></script>
    @endif
    </body>
</html>