@extends('layouts.admin')

@section('content')
<div class="contents">
    <div class="contents-area">
        <h1>ユーザー一覧</h1>
        @if(Session::has('flashmessage'))<div class="result-msg">{{ session('flashmessage') }}</div>@endif
        <div class="add-user"><div class="add-btn btn"><a href="{{ url('/admin/user/add') }}">新規登録</a></div></div>
        <div class="user">
        @foreach ($users as $user)
            <ul>
                <li><div class="username">{{ $user->user_name }}</div></li>
                <li><div class="edit-btn btn"><a href="{{ url('/admin/user/edit?id=' . $user->id) }}">編集</a></div></li>
                @if ($user->id != Auth::user()->id)
                <li><div class="delete-btn btn"><a href="{{ url('/admin/user/delete-confirm?id=' . $user->id) }}">削除</a></div></li>
                @endif
            </ul>
        @endforeach
        </div>
    </div>
</div>
@endsection

