@extends('layouts.admin')

@section('content')
<div class="contents">
    <div class="contents-area">
        <h1>カテゴリーの削除確認</h1>
        <div><span class="attention">以下の、カテゴリーを削除します。よろしければ「削除」ボタンを押下してください。<br>※一度削除したカテゴリーは元に戻せません。
        @if($relCat)
        <br>※このカテゴリーを使用している記事があります。
        @endif
        </span></div>
        <form method="post" action="{{ url('/admin/category/delete-proc') }}">
        @csrf
        <dl class="delete-form">
            <dt>カテゴリー表示名</dt>
            <dd class="article_parts">{{ $catData->disp_name }}</dd>
            <dt>カテゴリー名</dt>
            <dd class="article_parts">{{ $catData->category_name }}</dd>
            <dt>ソート順</dt>
            <dd class="article_parts">{{ $catData->sort_no }}</dd>
            <dt>最終更新日時</dt>
            <dd class="article_parts">{{ date('Y/n/j H:i:s', strtotime($catData->updated_at)) }}</dd>
        </dl>
        <input type="hidden" name="id" value="{{ $id }}">
        <div class="form-btn">
            <div class="submit-btn btn"><a href="{{ url('/admin/category') }}">戻る</a></div>
            <button class="submit-btn btn submit delete-btn" type="submit">削除</button>
        </div>
        </form>
        @if($relCat)
        <h2>このカテゴリーを使っている記事一覧</h2>
        <div class="article">
        @foreach($relCat as $data)
        <ul>
            <li><div class="post_title"><a href="{{ url($data->path) }}" target="_blank" rel="noopener noreferrer">{{ $data->title }}</a></div></li>
            <li>{!! $data->contents !!}</li>
            <li>
                <div class="post_status"><span class="@if($data->status == config('umekoset.status_publish')) publish-btn @else private-btn @endif disp-status">{{ config('umekoset.status.' . $data->status) }}</span></div>
                <div class="post_date">最終更新日<br>{{ date('Y/n/j H:i', strtotime($data->updated_at)) }}</div>
            </li>
        </ul>
        @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
