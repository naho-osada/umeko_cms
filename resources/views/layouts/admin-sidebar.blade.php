<div class="menu">
    <ul>
        <li><a href="{{ url('/admin/top') }}">TOP</a></li>
        <li><a href="{{ url('/admin/article') }}">記事一覧</a></li>
        <li><a href="{{ url('/admin/article/edit') }}">投稿</a></li>
        <li><a href="{{ url('/admin/category') }}">カテゴリー</a></li>
        <li><a href="{{ url('/admin/file') }}">ファイル</a></li>
        <li><a href="{{ Auth::user()->auth==config('umekoset.auth_admin') ? url('/admin/user') : url('/admin/user/edit?id=' . Auth::user()->id) }}">ユーザー</a></li>
        @if(config('umekoset.html_creater') && Auth::user()->auth==config('umekoset.auth_admin'))
        <li><a href="{{ url('/admin/html') }}">HTML生成</a></li>
        @endif
    </ul>
</div>
