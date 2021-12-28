@extends('layouts.admin')

@section('content')
<div class="contents">
    <div class="contents-area">
        <h1>ファイル情報の編集</h1>
        @if(Session::has('flashmessage'))<div class="result-msg">{{ session('flashmessage') }}</div>@endif
        @if($errors->all())<div class="result-msg">入力エラーがあります。確認してください。</div>@endif
        @if(Auth::user()->auth == config('umekoset.auth_admin') || Auth::user()->id == $data->user_id)
        <div>説明文のみ編集可能です。</div>
        <form method="post" action="{{ url('/admin/file/edit-proc') }}">
        @csrf
        @endif
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
            <dt>説明文<span class="description">画像の場合、alt属性になります。</span></dt>
            <dd>
            @if(Auth::user()->auth == config('umekoset.auth_admin') || Auth::user()->id == $data->user_id)
                <input name="description" type="text" value="{{ old('description', ($data->description ?? session('description'))) }}">@error('description')<p class="err-msg">{{ $message }}</p>@enderror
            @else
                {{ $data->description }}
            @endif
            </dd>
        </dl>
        <div class="form-btn">
            @if(Auth::user()->auth == config('umekoset.auth_admin') || Auth::user()->id == $data->user_id)
            <input type="hidden" name="id" value="{{ $id }}">
            <div class="submit-btn"><button class="btn submit" type="submit">更新</button></div>
            </form>
            @else
            <div class="submit-btn"><button class="btn submit"><a href="{{ url('/admin/file') }}">戻る</a></button</div>
            @endif
        </div>
    </div>
</div>
@endsection
