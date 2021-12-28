@extends('layouts.admin')

@section('content')
<div class="contents">
    <div class="contents-area">
        <h1>ユーザー情報削除確認</h1>
        <div><span class="attention">以下のユーザーを削除します。よろしければ「削除」ボタンを押下してください。</span></div>
        <form method="post" action="{{ url('/admin/user/delete-proc') }}">
        @csrf
        <dl class="form-dl">
            <dt>ユーザー名</dt>
            <dd>{{ $data->user_name }}</dd>
            <dt>メールアドレス</dt>
            <dd>{{ $data->email }}</dd>
            <dt>権限</dt>
            <dd>{{ $data->auth }}</dd>
        </dl>
        <div class="form-btn">
            <div class="submit-btn btn"><a href="{{ url('/admin/user') }}">戻る</a></div>
            <input type="hidden" name="id" value="{{ $data->id }}">
            <button class="submit-btn btn submit delete-btn" type="submit">削除</button>
        </div>
        </form>
    </div>
</div>
@endsection

