@extends('layouts.admin')

@section('content')
<div class="contents">
    <div class="contents-area">
        <h1>ファイル情報の削除確認</h1>
        <div><span class="attention">以下のファイルと情報を削除します。よろしければ「削除」ボタンを押下してください。<br>※一度ファイルと情報は元に戻せません。</span></div>
        <form method="post" action="{{ url('/admin/file/delete-proc') }}">
        @csrf
        <dl class="form-dl">
            <dt>ファイル</dt>
            <dd class="arcticle_parts article_thumbnail">
                <div class="thumbnail-area-file">
                    <div><img src="@if($data->thumbnail) {{ $data->thumbnail }} @endif"></div>
                </div>
                {{ $data->filename }}
            </dd>
            <dt>登録者</dt><dd>{{ $data->user_name }}</dd>
            <dt>登録日</dt><dd>{{ date('Y/n/j H:i:s', strtotime($data->created_at)) }}@if($data->created_at != $data->updated_at)（更新日&nbsp;{{ date('Y/n/j H:i:s', strtotime($data->updated_at)) }}）@endif</dd>
            <dt>説明文</dt><dd>{{ $data->description }}</dd>
        </dl>
        @if(!$postData->isEmpty())
        <h2>この画像が使われている記事一覧</h2>
        <div><span class="attention">※記事で使用しているファイルを削除した場合、その部分がリンク切れになります。</span></div>
        <div class="article">
        @foreach($postData as $data)
        <ul>
            <li>
                @if($data->icatchFlg)<div class="post_title"><span class="publish-btn disp-status">アイキャッチ画像</span></div>@endif
                @if($data->postFlg)<div class="post_title"><span class="publish-btn disp-status">本文で使用</span></div>@endif
                <div class="post_title"><a href="{{ url($data->path) }}" target="_blank" rel="noopener noreferrer">{{ $data->title }}</a></div>
            </li>
            <li>{!! $data->contents !!}</li>
            <li>
                <div class="post_status"><span class="@if($data->status == config('umekoset.status_publish')) publish-btn @else private-btn @endif disp-status">{{ config('umekoset.status.' . $data->status) }}</span></div>
                <div class="post_date">最終更新日<br>{{ date('Y/n/j H:i', strtotime($data->updated_at)) }}</div>
            </li>
        </ul>
        @endforeach
        </div>
        @endif
        <div class="form-btn">
            <div class="submit-btn btn"><a href="{{ url('/admin/file') }}">戻る</a></div>
            <button class="submit-btn btn submit delete-btn" type="submit">削除</button>
        </div>
        <input type="hidden" name="id" value="{{ $id }}">
        </form>
    </div>
</div>
@endsection
