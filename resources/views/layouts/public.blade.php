<!DOCTYPE html>
<html lang="ja" class="no-js no-svg">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="{{ $ogp['description'] }}" />
        <meta property="og:title" content="{{ $ogp['title'] }}" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="{{ $ogp['url'] }}" />
        <meta property="og:image" content="{{ $ogp['image'] }}" />
        <meta property="og:site_name" content="{{ $ogp['site_name'] }}" />
        <meta property="og:description" content="{{ $ogp['description'] }}" />
        <meta property="article:published_time" content="{{ $ogp['published_time'] }}" />
        <meta property="article:modified_time" content="{{ $ogp['modified_time'] }}" />
        @if(config('umekoset.twitter_name'))
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:site" content="{{ '@' . config('umekoset.twitter_name') }}" />
        <meta name="twitter:domain" content="{{ config('umekoset.twitter_name') }}" />
        <meta name="twitter:title" content="{{ $ogp['title'] }}" />
        <meta name="twitter:description" content="{{ $ogp['description'] }}" />
        <meta name="twitter:image" content="{{ $ogp['image'] }}" />
        @endif
        <title>{{ $ogp['title'] }}</title>
        <link href="{{ asset('css/style.css') }}" rel="stylesheet">
        <link rel="shortcut icon" href="{{ asset('images/umeko-hana-logo.png') }}" />
    </head>
    <body>
    <div class="header">
        <div class="cms-name"><a href="{{ url('/') }}"><img src="{{ asset('/images/umeko-logo.png') }}" alt="梅子"></a></div>
        <div class="header-right">
            <div>オープンソースのブログCMS「梅子」
                <a href="https://github.com/naho-osada/umeko_cms" target="_blank" rel="noopener"><img src="{{ asset('/images/icons/GitHub-Mark-32px.png') }}" alt="GitHub"></a>
            </div>
        </div>
    </div>
    <div>
        <ul class="header-navi">
            <li><a href="{{ url('/') }}">梅子</a></li>
            <li><a href="{{ url('/2021/12/about-umeko') }}">梅子について</a></li>
            <li><a href="https://github.com/naho-osada/umeko_cms">ソース（GitHub）</a></li>
            <li><a href="https://engineer-lady.com/about/">制作者プロフィール</a></li>
            <li><a href="https://engineer-lady.com/">エンジニア婦人ノート</a></li>
        </ul>
    </div>
    <div class="main">
        <div class="main-contents">
            @include('layouts.public-sidebar')
            @yield('content')
        </div>
    </div>
    <footer class="footer-area">
        <ul class="footer">
            <li><a href="{{ url('/') }}">梅子</a></li>
            <li><a href="{{ url('/2021/12/about-umeko') }}">梅子について</a></li>
            <li><a href="https://github.com/naho-osada/umeko_cms">ソース（GitHub）</a></li>
            <li><a href="https://engineer-lady.com/about/">制作者プロフィール</a></li>
            <li><a href="https://engineer-lady.com/">エンジニア婦人ノート</a></li>
        </ul>
        <div class="copyright">©&nbsp;2021-&nbsp;<a href="https://engineer-lady.com/about/">Naho&nbsp;Osada</a></div>
    </footer>
    </body>
</html>