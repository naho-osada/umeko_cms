@extends('layouts.admin')

@section('content')
<div class="contents">
    <div class="contents-area">
        <h1>記事一覧</h1>
        @if(Session::has('flashmessage'))<div class="result-msg">{{ session('flashmessage') }}</div>@endif
        <div class="add-user"><div class="add-btn btn"><a href="{{ url('/admin/article/edit') }}">新しい記事を書く</a></div></div>
        @if($article->isEmpty())
            <div class="result-msg">表示する情報がありません。@if($search['status'] == '')新しい記事を書いてみませんか？@else別の条件で検索してください。@endif</div>
        @endif
        @include('admin.article.search')
        @if(!$article->isEmpty())
        {{ $article->appends($search)->links('pager/default') }}
        @endif
        <div class="article">
        @foreach ($article as $data)
            <ul>
                <li><div class="post_title"><a href="{{ url('/admin/article/edit?id=' . $data->id) }}">{{ $data->title }}</a></div></li>

                <li>
                    <div class="post_status"><span class="@if($data->status == config('umekoset.status_publish')) publish-btn @else private-btn @endif disp-status">{{ config('umekoset.status.' . $data->status) }}</span></div>
                    <div class="post_date">{{ date('Y/n/j H:i', strtotime($data->publish_at)) }}</div>
                    <div class="post_date">作成者：{{ $data->user_name }}</div>
                    @if($data->user_id != $data->updated_user_id)<div class="post_date">更新者：{{ $data->updated_user }}</div>@endif
                </li>

                <li class="article-btn"><ul>
                    <li><div class="edit-btn btn"><a href="{{ url('/admin/article/edit?id=' . $data->id) }}">編集</a></div></li>
                    <li>@if ($data->status == config('umekoset.status_publish'))<div class="private-btn btn"><a href="{{ url('/admin/article/private?id=' . $data->id) }}">非公開</a></div>@else<div class="publish-btn btn"><a href="{{ url('/admin/article/publish?id=' . $data->id) }}">公開</a></div>@endif</li>
                    @if (Auth::user()->auth == config('umekoset.auth_admin'))
                    <li><div class="delete-btn btn"><a href="{{ url('/admin/article/delete-confirm?id=' . $data->id) }}">削除</a></div></li>
                    @endif
                </ul></li>
            </ul>
        @endforeach
        </div>
        @if(!$article->isEmpty())
        {{ $article->appends($search)->links('pager/default') }}
        @endif
    </div>
</div>
@endsection

