@extends('layouts.admin')

@section('content')
<div class="contents">
    <div class="contents-area">
        <h1>カテゴリ一覧</h1>
        @if(Session::has('flashmessage'))<div class="result-msg">{{ session('flashmessage') }}</div>@endif
        <div class="add-category"><div class="add-btn btn"><a href="{{ url('/admin/category/edit') }}">新規カテゴリを作成</a></div></div>
        @if($category->isEmpty())
            <div class="result-msg">表示する情報がありません。</div>
        @endif
        @if(Auth::user()->auth == config('umekoset.auth_user'))<div>自分が作成したカテゴリーのみ編集できます。削除はできません。</div>@endif
        <div>（）内の数はそのカテゴリを使用している記事の数です。</div>
        <div class="category">
        @foreach ($category as $data)
             <ul>
                <li><div class="category_name">
                    @if(Auth::user()->auth == config('umekoset.auth_admin') || Auth::user()->id == $data->user_id)<a href="{{ url('/admin/category/edit?id=' . $data->id) }}">@endif
                    {{ $data->disp_name }}（@if($data->article_cnt == null) 0 @else {{ $data->article_cnt }} @endif）
                    @if(Auth::user()->auth == config('umekoset.auth_admin') || Auth::user()->id == $data->user_id)</a>@endif</div></li>
                @if(Auth::user()->auth == config('umekoset.auth_admin') || Auth::user()->id == $data->user_id)
                <li><div class="edit-btn btn"><a href="{{ url('/admin/category/edit?id=' . $data->id) }}">編集</a></div></li>
                @endif
                @if(Auth::user()->auth == config('umekoset.auth_admin'))
                <li><div class="delete-btn btn"><a href="{{ url('/admin/category/delete-confirm?id=' . $data->id) }}">削除</a></div></li>
                @endif
            </ul>
        @endforeach
        </div>
    </div>
</div>
@endsection

