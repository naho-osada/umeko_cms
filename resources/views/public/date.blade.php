@extends('layouts.public')

@section('content')
<div class="contents">
    <div class="article-area">
        <h2>{{ $dispDate }}の記事</h2>
        @if(!empty($articles[0]))
        <ul class="article_related">
            @foreach($articles as $key=>$data)
            <li>
                @if(isset($data->icatch_thumbnail))
                <div><a href="{{ $data->url }}" title="{{ $data->title }}" class="link_style_none"><img src="@if($data->icatch_thumbnail) {{ $data->icatch_thumbnail }} @endif" class="related_icatch"></a></div>
                @else
                <div><a href="{{ $data->url }}" title="{{ $data->title }}" class="link_style_none"><img src="{{ asset(config('umekoset.noimage')) }}" alt="NoImage" class="related_icatch"></a></div>
                @endif
                @if(!empty($relCategories[$key]))
                <div>
                    <ul class="article_category">
                    @foreach($relCategories[$key] as $catData)
                        <li><a href="{{ asset($catData['url']) }}" class="link_style_none">{{ $catData['name'] }}</a></li>
                    @endforeach
                    </ul>
                </div>
                @endif
                <div><a href="{{ $data->url }}">{{ $data->title }}</a></div>
            </li>
            @endforeach
        </ul>
        @else
        <div>{{ config('umekoset.default_message') }}</div>
        @endif
    </div>
    @if(!$articles->isEmpty())
        {{ $articles->links('pager/default') }}
    @endif
</div>
@endsection
