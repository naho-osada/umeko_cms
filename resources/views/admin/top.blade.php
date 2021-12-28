@extends('layouts.admin')

@section('content')
<div class="contents">
    <div class="contents-area">
        <div class="dashboard">
            <div>ようこそ、おかえりなさい！ただいま、{{ $date }}です。</div>
            <div>今日は何をしますか？</div>
        </div>
        @if(!$article->isEmpty())
        <h2>最近更新された記事</h2>
        <div class="article">
            @foreach($article as $data)
            <ul>
                <li><div class="post_title"><a href="{{ url('/admin/article/edit?id=' . $data->id) }}">{{ $data->title }}</a></div></li>
                <li>{!! $data->contents !!}</li>
                <li>
                    <div class="post_status"><span class="@if($data->status == config('umekoset.status_publish')) publish-btn @else private-btn @endif disp-status">{{ config('umekoset.status.' . $data->status) }}</span></div>
                    <div class="post_date">最終更新日<br>{{ date('Y/n/j H:i', strtotime($data->updated_at)) }}</div>
                </li>
            </ul>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection