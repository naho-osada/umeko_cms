@extends('layouts.admin')

@section('content')
<div class="form-login">
    <h1>{{ __('Login') }}</h1>
    <form method="post" action="{{ route('login') }}">
    @csrf
    <dl class="form-dl login">
        <dt><label for="email">{{ __('E-Mail Address') }}</label><span class="require"></span></dt>
        <dd><input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" required autocomplete="email" autofocus>
        @error('email')<p class="err-msg">{{ $message }}</p>@enderror</dd>
        <dt><label for="password">{{ __('Password') }}</label><span class="description">半角英数字で入力してください。</span><span class="require"></span></dt>
        <dd><input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
        @error('password')<p class="err-msg">{{ $message }}</p>@enderror</dd>
        @if(config('umekoset.login_captcha'))
        <dt><label for="captcha" class="col-md-4 control-label">画像認証</label><span class="description">画像の英数字を入力してください。</span><span class="require"></span></dt>
        <dd><input id="captcha" type="text" class="form-control" name="captcha">
        <div class="captcha-area">
            {!! captcha_img() !!}
        </div>
        @error('captcha')<p class="err-msg">{{ $message }}</p>@enderror</dd>
        @endif
    </dl>
    <div class="remember">
        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
        <label class="form-check-label" for="remember">
            {{ __('Remember Me') }}
        </label>
    </div>
    <div class="login-btn"><button class="btn submit" type="submit">{{ __('Login') }}</button></div>
    </form>
</div>
@endsection
