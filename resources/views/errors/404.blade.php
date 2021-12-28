<!DOCTYPE html>
<html lang="ja" class="no-js no-svg">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>梅子-umeko-</title>
        @auth
        <link href="{{ asset('css/admin/style.css') }}" rel="stylesheet">
        @else
        <link href="{{ asset('css/style.css') }}" rel="stylesheet">
        @endauth
    </head>
    <body>
    @auth
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
            <div class="contents">
                <div class="contents-area">
                    <h2>お探しのページは見つかりませんでした。</h2>
                    <a href="{{ url('admin/top') }}">管理画面トップページへ戻る</a>
                </div>
            </div>
        </div>
    </div>
    <footer class="footer-area">
        <ul class="footer">
            <li><a href="{{ url('/2021/12/about') }}">梅子について</a></li>
            <li><a href="https://engineer-lady.com/about/">制作者プロフィール</a></li>
            <li><a href="https://github.com/naho-osada/umeko">ソース（GitHub）</a></li>
        </ul>
        <div class="copyright">©&nbsp;2021-&nbsp;<a href="https://engineer-lady.com/about/">Naho&nbsp;Osada</a></div>
    </footer>
    @if(Request::is('admin/article/edit'))
        <script src="{{ mix('js/admin/trumbowyg.js') }}"></script>
        <script src="{{ mix('js/admin/article.js') }}"></script>
        <link rel="stylesheet" href="{{ asset('css/admin/trumbowyg.css') }}">
    @endif
    @else
    <div class="header">
        <div class="cms-name"><h1><a href="{[ url('/') }}"><img src="{{ asset('/images/umeko-logo.png') }}" alt="梅子"></a></h1></div>
        <div class="header-right">
            <div>オープンソースのブログCMS「梅子」
                <a href="https://github.com/naho-osada/umeko" target="_blank" rel="noopener"><img src="{{ asset('/images/icons/GitHub-Mark-32px.png') }}" alt="GitHub"></a>
            </div>
        </div>
    </div>
    <div class="main">
        <div class="main-contents">
            <div class="contents">
                <div class="contents-area">
                    <div class="article-area">
                        <p>アクセスしようとしたページは削除、変更されたか、現在利用できない可能性があります。</p>
                        <p>お手数ですが、もう一度梅子のトップページよりお越しください。</p>
                        <p><a href="{{ url('/') }}">トップページへ移動</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div>
        <ul class="footer">
                <li class="tweet-btn sns-btn"><a href="http://twitter.com/share?text=&url=" rel="nofollow" onclick="return sns_window(this, 400, 600);" title="Twitterでシェア"><img src="{{ asset('/images/icons/twitter.png') }}"/></a></li>
                <li class="facebook-btn sns-btn"><a href="http://www.facebook.com/share.php?u=" onclick="return sns_window(this, 800, 600);" title="Facebookでシェア"><img src="{{ asset('/images/icons/facebook.png') }}" /></a></li>
                <li class="line-btn sns-btn"><a href="//line.me/R/msg/text/?%0A" target="_blank" title="LINEに送る"><img src="{{ asset('/images/icons/line.png') }}" /></a></li>
                <li class="hatena-btn sns-btn"><a href="https://b.hatena.ne.jp/entry/" class="hatena-bookmark-button" data-hatena-bookmark-layout="touch" data-hatena-bookmark-width="40" data-hatena-bookmark-height="40" title="このエントリーをはてなブックマークに追加"><img src="https://b.st-hatena.com/images/v4/public/entry-button/button-only@2x.png" alt="このエントリーをはてなブックマークに追加" width="20" height="20" style="border: none;" /></a><script type="text/javascript" src="https://b.st-hatena.com/js/bookmark_button.js" charset="utf-8" async="async"></script></li>
        </ul>
    </div>
    <footer class="footer-area">
        <ul class="footer">
            <li><a href="{{ url('/') }}">梅子</a></li>
            <li><a href="{{ url('/2021/12/about') }}">梅子について</a></li>
            <li><a href="https://github.com/naho-osada/umeko">ソース（GitHub）</a></li>
            <li><a href="https://engineer-lady.com/about/">制作者プロフィール</a></li>
            <li><a href="https://engineer-lady.com/">エンジニア婦人ノート</a></li>
        </ul>
        <div class="copyright">©&nbsp;2021-&nbsp;<a href="https://engineer-lady.com/about/">Naho&nbsp;Osada</a></div>
    </footer>
    @endauth
    </body>
</html>
