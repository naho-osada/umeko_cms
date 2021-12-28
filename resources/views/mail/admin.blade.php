<div>{{ $date }}にユーザー名「{{ Auth::user()->user_name }}」がログインしました。</div>
<hr>
<div>メールアドレス : {{ Auth::user()->email }}</div>
<div>ユーザー名 : {{ Auth::user()->user_name }}</div>
<div>IP : {{ $ip }}</div>
<hr>
<div>***</div>
<div>このメールは{{ config('umekoset.site_name') }}より自動送信されています。返信はできません。</div>
