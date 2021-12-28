@extends('layouts.admin')

@section('content')
<div class="contents">
    <div class="contents-area">
        <h1>記事の削除確認</h1>
        <div><span class="attention">以下の記事を削除します。よろしければ「削除」ボタンを押下してください。<br>※一度削除した記事は元に戻せません。</span></div>
        <form method="post" action="{{ url('/admin/article/delete-proc') }}">
        @csrf
        <dl class="delete-form">
            @if($data->icatch_thumbnail)
            <dt>アイキャッチ画像（記事サムネイル）</dt>
            <dd class="arcticle_parts article_thumbnail">
                <div><img src="@if($data->icatch_thumbnail) {{ $data->icatch_thumbnail }} @endif" id="icatch-thumbnail" class="thumbnail"></div>
            </dd>
            @endif
            <dt>ページ名称</dt>
            <dd class="article_parts">{{ $data->path }}</dd>
            <dt>記事タイトル</dt>
            <dd class="article_parts article_title">{{ $data->title }}</dd>
            <dt>記事本文</dt>
            <dd class="article_parts">{!! $data->contents !!}</dd>
            <dt>SEO description</dt>
            <dd class="article_parts">{{ $data->seo_description }}</dd>
            <dt>ステータス</dt>
            <dd class="article_parts">{{ config('umekoset.status.' . $data->status) }}</dd>
            <dt>編集権限</dt>
            <dd class="article_parts article_select">{{ config('umekoset.article_auth.' . $data->article_auth) }}</dd>
            <dt>公開日時</dt>
            <dd class="article_parts">{{ date('Y/n/j H:i:s', strtotime($data->publish_at)) }}（公開者：{{ $data->user_name }}）</dd>
            <dt>最終更新日時</dt>
            <dd class="article_parts">{{ date('Y/n/j H:i:s', strtotime($data->updated_at)) }}（最終更新者：{{ $data->updated_user }}）</dd>
        </dl>
        <input type="hidden" name="id" value="{{ $id }}">
        <div class="form-btn">
            <div class="submit-btn btn"><a href="{{ url('/admin/article') }}">戻る</a></div>
            <button class="submit-btn btn submit delete-btn" type="submit">削除</button>
        </div>
        </form>
    </div>
</div>
@endsection

