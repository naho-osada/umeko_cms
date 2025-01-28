@extends('layouts.admin')

@section('content')
<div class="contents">
    <div class="contents-area">
        <h2>HTML生成する記事一覧</h2>
        <p>「実行」すると以下のページ（<strong>{{ $cnt }}件</strong>）をすべてHTML出力します。</p>
        <form method="post" action="{{ url('/admin/html/make') }}">

        @csrf
        <p>出力先のドメイン名&nbsp;<input id="domain" type="text" value="{{ config('umekoset.html_domain') }}" size=40>
            <span class="attention"><small>※https://&nbsp;(またはhttp://)から入力します</small></span><br>
            <small>現在のドメイン&nbsp;({{ url('') }})&nbsp;を指定したドメインに変換してHTMLを生成します。</small></p>
        <div class="maker-btn">
            <!-- <button class="btn submit submit-btn" id="html-maker" type="submit" formaction="{{ url('/admin/html/make') }}">HTML生成</button> -->
            <button class="btn submit submit-btn" id="html-maker" type="submit">HTML生成</button>
        </div>
        </form>

        <div class="list-html">
            <ul>
            @foreach($pages as $data)
                <li><a href="{{ $data->url }}">{{ $data->title }}</a>&nbsp;{{ $data->update }}</li>
            @endforeach
            </ul>
        </div>
    </div>
    <div id="loading" class="loaded">
        <div class="loader" id="loading-img"></div>
    </div>
</div>
@endsection