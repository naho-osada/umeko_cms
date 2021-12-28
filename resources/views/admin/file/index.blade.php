@extends('layouts.admin')

@section('content')
<div class="contents">
    <div class="contents-area">
        <h1>ファイル一覧</h1>
        @if(Session::has('flashmessage'))<div class="result-msg">{{ session('flashmessage') }}</div>@endif
        @if($file->isEmpty())
            <div class="result-msg">表示する情報がありません。</div>
        @endif
        <div class="file-list">
        @foreach ($file as $data)
                <ul class="file-data">
                    <li class="disp-file"><a href="{{ url('/admin/file/edit?id=' . $data->id) }}"><img src="{{ $data->thumbnail }}" alt="{{ $data->description }}"></a></li>
                    <li class="file-description">{{ $data->description }}</li>
                    <li class="file-btn">
                        <div class="file-btn">
                            @if(Auth::user()->auth == config('umekoset.auth_admin') || Auth::user()->id == $data->user_id)
                            <div class="edit-btn btn"><a href="{{ url('/admin/file/edit?id=' . $data->id) }}">編集</a></div>
                            @else
                            <div class="private-btn btn"><a href="{{ url('/admin/file/edit?id=' . $data->id) }}">詳細</a></div>
                            @endif
                            @if(Auth::user()->auth == config('umekoset.auth_admin'))
                            <div class="delete-btn btn"><a href="{{ url('/admin/file/delete-confirm?id=' . $data->id) }}">削除</a></div>
                            @endif
                        </div>
                    </li>
                </ul>
        @endforeach
        </div>
        @if(!$file->isEmpty())
        {{ $file->links('pager/default') }}
        @endif
    </div>
</div>
@endsection

