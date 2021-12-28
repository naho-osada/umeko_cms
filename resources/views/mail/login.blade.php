<div>{{ $date }}に{{ config('umekoset.site_name') }}へログインしました。</div>
<hr>
<div>メールアドレス : {{ Auth::user()->email }}</div>
<div>ユーザー名 : {{ Auth::user()->user_name }}</div>
<hr>
<div>***</div>
<div>このメールは{{ config('umekoset.site_name') }}より自動送信されています。返信はできません。</div>
<div>このメールに覚えがない場合、メールアドレスの登録に誤りがあったかもしれません。本メールを破棄していただけますよう、よろしくお願いします。</div>
