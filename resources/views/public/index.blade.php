@extends('layouts.public')

@section('content')
<div class="contents">
    <div class="contents-area">
        <div class="article-area">
            <p>梅子はウェブページを管理するCMSアプリケーションです。</p>
            <p>Naho Osadaによって開発されました。</p>
            <p>梅子は主にブログのような、「更新頻度がそれなりに高い」ものに向いています。</p>
            <p>**</p>
            <p>Umeko is a CMS application that manages web pages.</p>
            <p>Developed by Naho Osada.</p>
            <p>It is mainly suitable for things such as blogs that are "updated frequently".</p>
            <h2>あなたが梅子-Umeko-を使うことで得られるメリット -Benefits you get from using Umeko</h2>
            <ul
                ><li>インターネット上にあなたのブログを公開できる</li>
                <li>強制更新がないので、安定して使い続けられる</li>
                <li>更新があっても、それを適用するかはあなた自身が選択できる</li>
                <li>複雑な機能がないので、新しく覚えることが少ない</li>
                <li>機能の追加、編集ができる（PHP、HTML、CSSなどの基本的な知識があれば。Laravelの知識があると尚良いでしょう）</li>
            </ul>
            <p>**</p>
            <ul>
                <li>You can publish your blog on the internet.</li>
                <li>Since there is no forced update, you can continue to use it stably.</li>
                <li>Even if there is an update, you can choose whether to apply it.</li>
                <li>There are no complicated functions, so there is little new learning.</li>
                <li>You can add and edit functions (if you have basic knowledge of PHP, HTML, CSS, etc., it is better to have knowledge of Laravel)</li>
            </ul>
            <ul class="footer">
                <li class="tweet-btn sns-btn"><a href="http://twitter.com/share?text=&url={{ config('app.url') }}" rel="nofollow" onclick="return sns_window(this, 400, 600);" title="Twitterでシェア"><img src="{{ asset('/images/icons/twitter.png') }}"/></a></li>
                <li class="facebook-btn sns-btn"><a href="http://www.facebook.com/share.php?u={{ config('app.url') }}" onclick="return sns_window(this, 800, 600);" title="Facebookでシェア"><img src="{{ asset('/images/icons/facebook.png') }}" /></a></li>
                <li class="line-btn sns-btn"><a href="//line.me/R/msg/text/?%0A{{ config('app.url') }}" target="_blank" title="LINEに送る"><img src="{{ asset('/images/icons/line.png') }}" /></a></li>
                <li class="hatena-btn sns-btn"><a href="https://b.hatena.ne.jp/entry/{{ config('app.url') }}" class="hatena-bookmark-button" data-hatena-bookmark-layout="touch" data-hatena-bookmark-width="40" data-hatena-bookmark-height="40" title="このエントリーをはてなブックマークに追加"><img src="https://b.st-hatena.com/images/v4/public/entry-button/button-only@2x.png" alt="このエントリーをはてなブックマークに追加" width="20" height="20" style="border: none;" /></a><script type="text/javascript" src="https://b.st-hatena.com/js/bookmark_button.js" charset="utf-8" async="async"></script></li>
            </ul>
            <h2>最新の記事</h2>
            @if(!empty($article[0]))
                @foreach($article as $key=>$data)
                <div class="article-list">
                    @if($data->icatch_thumbnail)
                    <div class="list-icatch"><img src="{{ $data->icatch_thumbnail }}" alt="{{ $data->description }}"></div>
                    @endif
                    <div class="top-contents">
                        <h3><a href="{{ $data->url }}">{{ $data->title }}</a></h3>
                        @if(!empty($relCategories[$key]))
                        <div>
                            <ul class="article_category">
                            @foreach($relCategories[$key] as $catData)
                                <li><a href="{{ asset($catData['url']) }}" class="link_style_none">{{ $catData['name'] }}</a></li>
                            @endforeach
                            </ul>
                        </div>
                        @endif
                        <div>{!! $data->contents !!}</div>
                        <div class="post_date">{{ date('Y/n/j H:i', strtotime($data->publish_at)) }}&nbsp;公開</div>
                    </div>
                </div>
                @endforeach
            @else
            <div>{{ config('umekoset.default_message') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
