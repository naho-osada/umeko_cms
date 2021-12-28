@extends('layouts.admin')

@section('content')
<div class="contents">
    <div class="contents-area">
        <h1>カテゴリーの投稿</h1>
        @if(Session::has('flashmessage'))<div class="result-msg">{{ session('flashmessage') }}</div>@endif
        @if($errors->all())<div class="result-msg">入力エラーがあります。確認してください。</div>@endif
        <form method="post" action="{{ url('/admin/category/edit-proc') }}" enctype="multipart/form-data">
        @csrf
        <dl class="form-dl">
            <dt>カテゴリー表示名<span class="description">サイトに表示する名称です。</span><span class="require"></span></dt>
            <dd><input name="disp_name" type="text" value="{{ old('disp_name', ($data->disp_name ?? session('disp_name'))) }}">@error('disp_name')<p class="err-msg">{{ $message }}</p>@enderror</dd>
            <dt>カテゴリー名<span class="description">URLなどで使用する内部名称です。</span><span class="require"></span></dt>
            <dd><input name="category_name" type="text" value="{{ old('category_name', ($data->category_name ?? session('category_name'))) }}">@error('category_name')<p class="err-msg">{{ $message }}</p>@enderror</dd>
            <dt>ソート順<span class="description">表示順の番号です。1番が先頭です。重複する場合は内部IDの早い順です。</span></dt>
            <dd class="category_sort">
                <select name="sort_no">
                    <option value="">選択してください</option>
                @for($i=1; $i<=$sortNum; $i++)
                    <option value="{{ $i }}" @if($i == old('sort_no', ($data->sort_no ?? session('sort_no')))) selected @endif>{{ $i }}</option>
                @endfor
                </select>
                @error('sort_no')<p class="err-msg">{{ $message }}</p>@enderror
            </dd>
        </dl>
        <input type="hidden" name="id" value="{{ $id }}">
        <div class="submit-btn"><button class="btn submit" type="submit">@if(empty($id)) 投稿 @else 更新@endif</button></div>
        </form>
    </div>
</div>
@endsection
