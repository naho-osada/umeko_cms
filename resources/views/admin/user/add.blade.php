@extends('layouts.admin')

@section('content')
<div class="contents">
    <div class="contents-area">
        <h1>ユーザー情報登録</h1>
        @if(Session::has('flashmessage'))<div class="result-msg">{{ session('flashmessage') }}</div>@endif
        <form method="post" action="{{ url('/admin/user/add-proc') }}">
        @csrf
        <dl class="form-dl">
            <dt>ユーザー名<span class="description">表示する名称です。</span><span class="require"></span></dt>
            <dd><input name="user_name" type="text" value="{{ old('user_name') }}">@error('user_name')<p class="err-msg">{{ $message }}</p>@enderror</dd>
            <dt>メールアドレス<span class="require"></span></dt>
            <dd><input name="email" type="email" value="{{ old('email') }}">@error('email')<p class="err-msg">{{ $message }}</p>@enderror</dd>
            <dt>パスワード<span class="description">半角英数字で入力してください。</span><span class="require"></span></dt>
            <dd><input name="password" type="password" value="">@error('password')<p class="err-msg">{{ $message }}</p>@enderror</dd>
            <dt>権限</dt>
            <dd>
            <select name="auth">
            @foreach(config('umekoset.auth') as $key=>$auth)
                <option value="{{ $key }}" @if($key==old('auth')) selected @endif>{{ $auth }}</option>
            @endforeach
            </select>
            @error('auth')<p class="err-msg">{{ $message }}</p>@enderror
            </dd>
        </dl>
        <div class="submit-btn"><button class="btn submit" type="submit">登録</button></div>
        </form>
    </div>
</div>
@endsection

