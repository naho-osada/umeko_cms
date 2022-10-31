@if($pager == 'preview')
<div class="preview-row">Preview</div>
@endif

@extends('layouts.public')

@section('content')
<div class="contents">
    <div class="article-area">
        <h1 class="article_title"><a href="{{ $article->url }}" title="{{ $article->title }}" class="link_style_none">{{ $article->title }}</a></h1>
        <div class="article_post">{{ date('Y/n/j H:i', strtotime($article->publish_at)) }}@if($article->publish_at != $article->updated_at && date('Ymd', strtotime($article->publish_at)) < date('Ymd', strtotime($article->updated_at)))（更新日&nbsp;{{ date('Y/n/j H:i', strtotime($article->updated_at)) }}）@endif</div>
        @if(!empty($article->icatch_thumbnail))
        <div class="article_icatch"><a href="{{ $article->url }}" title="{{ $article->title }}" class="link_style_none"><img src="@if($article->icatch_thumbnail){{ $article->icatch_thumbnail }}@endif"id="icatch-thumbnail"></a></div>
        @endif
        @if(!empty($relCategories))
        <div>
            <ul class="article_category">
            @foreach($relCategories as $category)
                <li><a href="{{ asset($category['url']) }}" class="link_style_none">{{ $category['name'] }}</a></li>
            @endforeach
            </ul>
        </div>
        @endif
        <div class="article-contents">{!! $article->contents !!}</div>
        <div class="author_data">by {{ $article->user_name }}</div>
    </div>
    @if($pager != 'preview')
    <ul class="footer">
        <li class="tweet-btn sns-btn"><a href="http://twitter.com/share?text=&url={{ $article->url }}" rel="nofollow" onclick="return sns_window(this, 400, 600);" title="Twitterでシェア"><img src="{{ asset('/images/icons/twitter.png') }}"/></a></li>
        <li class="facebook-btn sns-btn"><a href="http://www.facebook.com/share.php?u={{ $article->url }}" onclick="return sns_window(this, 800, 600);" title="Facebookでシェア"><img src="{{ asset('/images/icons/facebook.png') }}" /></a></li>
        <li class="line-btn sns-btn"><a href="//line.me/R/msg/text/?%0A{{ $article->url }}" target="_blank" title="LINEに送る"><img src="{{ asset('/images/icons/line.png') }}" /></a></li>
        <li class="hatena-btn sns-btn"><a href="https://b.hatena.ne.jp/entry/{{ $article->url }}" class="hatena-bookmark-button" data-hatena-bookmark-layout="touch" data-hatena-bookmark-width="40" data-hatena-bookmark-height="40" title="このエントリーをはてなブックマークに追加"><img src="https://b.st-hatena.com/images/v4/public/entry-button/button-only@2x.png" alt="このエントリーをはてなブックマークに追加" width="20" height="20" style="border: none;" /></a><script type="text/javascript" src="https://b.st-hatena.com/js/bookmark_button.js" charset="utf-8" async="async"></script></li>
    </ul>
    @endif
    @if($pager && $pager != 'preview')
    <nav class="article_pager">
        @if($pager['before'])
        <div>
            <div><a href="{{ $pager['before']->url }}" class="link_style_none">前の記事</a></div>
            <div class="article_pager_box">
                @if(isset($pager['before']->icatch_thumbnail))
                <div><a href="{{ $pager['before']->url }}" title="{{ $pager['before']->title }}" class="link_style_none"><img src="@if($pager['before']->icatch_thumbnail) {{ $pager['before']->icatch_thumbnail }} @endif" alt="{{ $pager['before']->title }}" class="pager_icatch"></a></div>
                @else
                <div><a href="{{ $pager['before']->url }}" title="{{ $pager['before']->title }}" class="link_style_none"><img src="{{ asset(config('umekoset.noimage')) }}" alt="NoImage" class="pager_icatch"></a></div>
                @endif
                <div><a href="{{ $pager['before']->url }}" class="link_style_none">{{ $pager['before']->title }}</a></div>
            </div>
        </div>
        @else
        <div>&nbsp;</div>
        @endif
        @if($pager['after'])
        <div>
            <div><a href="{{ $pager['after']->url }}" class="link_style_none">次の記事</a></div>
            <div class="article_pager_box">
                <div><a href="{{ $pager['after']->url }}" class="link_style_none">{{ $pager['after']->title }}</a></div>
                @if(isset($pager['after']->icatch_thumbnail))
                <div><a href="{{ $pager['after']->url }}" title="{{ $pager['after']->title }}" class="link_style_none"><img src="@if($pager['after']->icatch_thumbnail) {{ $pager['after']->icatch_thumbnail }} @endif" alt="{{ $pager['after']->title }}" class="pager_icatch"></a></div>
                @else
                <div><a href="{{ $pager['after']->url }}" title="{{ $pager['after']->title }}" class="link_style_none"><img src="{{ asset(config('umekoset.noimage')) }}" alt="NoImage" class="pager_icatch"></a></div>
                @endif
            </div>
        </div>
        @else
        <div>&nbsp;</div>
        @endif
    </nav>
    @endif
    @if(is_object($relArticles) && count($relArticles) > 0)
    <h2>関連記事</h2>
    <ul class="article_related">
        @foreach($relArticles as $data)
        <li>
            @if(isset($data->icatch_thumbnail))
            <div><a href="{{ $data->url }}" title="{{ $data->title }}" class="link_style_none"><img src="@if($data->icatch_thumbnail) {{ $data->icatch_thumbnail }} @endif" class="related_icatch"></a></div>
            @else
            <div><a href="{{ $data->url }}" title="{{ $data->title }}" class="link_style_none"><img src="{{ asset(config('umekoset.noimage')) }}" alt="NoImage" class="related_icatch"></a></div>
            @endif
            <div><a href="{{ $data->url }}">{{ $data->title }}</a></div>
        </li>
        @endforeach
    </ul>
    @endif
</div>
@endsection
