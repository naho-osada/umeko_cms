@extends('layouts.admin')

@section('content')
<div class="contents">
    <div class="contents-area">
        <h1>記事の投稿</h1>
        @if(Session::has('flashmessage'))<div class="result-msg">{{ session('flashmessage') }}</div>@endif
        @if($errors->all())<div class="result-msg">入力エラーがあります。確認してください。</div>@endif
        <form method="post" action="{{ url('/admin/article/edit-proc') }}" enctype="multipart/form-data">
        @csrf
        <dl>
            <dt>公開日時@if(isset($data->user_name))（公開者：{{ $data->user_name }}）@endif</dt>
            <dd class="article_parts article_select">
                <div>
                    <select name="open_year">
                    @for($i=$nowYear - config('umekoset.open_year_set'); $i <= $nowYear + config('umekoset.open_year_set'); $i++)
                        <option value="{{$i}}" @if($i == old('open_year', ($data->open_year ?? session('open_year')))) selected @endif>{{ $i }}</option>
                    @endfor
                    </select>年
                    <select name="open_month">
                    @for($i=1; $i <= 12; $i++)
                        <option value="{{sprintf('%02d', $i)}}" @if($i == old('open_month', ($data->open_month ?? session('open_month')))) selected @endif>{{ $i }}</option>
                    @endfor
                    </select>月
                    <select name="open_day">
                    @for($i=1; $i <= 31; $i++)
                        <option value="{{sprintf('%02d', $i)}}" @if($i == old('open_day', ($data->open_day ?? session('open_day')))) selected @endif>{{ $i }}</option>
                    @endfor
                    </select>日
                </div>
                <div>
                    <select name="open_hour">
                    @for($i=1; $i <= 24; $i++)
                        <option value="{{sprintf('%02d', $i)}}" @if($i == old('open_hour', ($data->open_hour ?? session('open_hour')))) selected @endif>{{ sprintf('%02d', $i) }}</option>
                    @endfor
                    </select>時
                    <select name="open_min">
                    @for($i=0; $i <= 59; $i++)
                        <option value="{{sprintf('%02d', $i)}}" @if($i == old('open_min', ($data->open_min ?? session('open_min')))) selected @endif>{{ sprintf('%02d', $i) }}</option>
                    @endfor
                    </select>分
                </div>
                @error('publish_at')<p class="err-msg">{{ $message }}</p>@enderror
            </dd>
            @if(isset($data->updated_at))
            <dt>最終更新日時@if(isset($data->updated_user))（最終更新者：{{ $data->updated_user }}）@endif</dt>
            <dd class="article_parts">{{ date('Y/n/j H:i:s', strtotime(old('updated_at', ($data->updated_at ?? session('updated_at'))))) }}</dd>
            @endif
            <dt>ステータス</dt>
            <dd class="article_parts article_select">
                <select name="status">
                @foreach(config('umekoset.status') as $key=>$status)
                <option value="{{$key}}" @if($key == old('status', ($data->status ?? session('status')))) selected @endif>{{ $status }}</option>
                @endforeach
                </select>
                @error('status')<p class="err-msg">{{ $message }}</p>@enderror
                @if(isset($data->status))
                @if($data->status == config('umekoset.status_publish') && strtotime($data->publish_at) <= time())
                <div>公開中ページ：<a href="{{ url('/' . $data->open_year . '/' . $data->open_month . '/' . $data->path . '/') }}" target="_blank" rel="noopener">{{ old('post_title', ($data->title ?? session('post_title'))) }}</a></div>
                @endif
                @endif
            </dd>
            @if(Auth::user()->auth == config('umekoset.auth_admin'))
            <dt>編集権限</dt>
            <dd class="article_parts article_select">
                <select name="article_auth">
                @foreach(config('umekoset.article_auth') as $key=>$auth)
                <option value="{{$key}}" @if($key == old('article_auth', ($data->article_auth ?? session('article_auth')))) selected @endif>{{ $auth }}</option>
                @endforeach
                </select>
                @error('article_auth')<p class="err-msg">{{ $message }}</p>@enderror
            </dd>
            @else
            <input type="hidden" name="article_auth" value="{{ config('umekoset.article_auth_creator') }}">
            @endif
            <dt>アイキャッチ画像（記事サムネイル）</dt>
            <dd class="arcticle_parts article_thumbnail">
                <input type="file" name="icatch" accept="image/*" id="icatch" class="article_icatch">
                @error('icatch')<p class="err-msg">{{ $message }}</p>@enderror
                @error('filetype')<p class="err-msg">{{ $message }}</p>@enderror
                <div class="thumbnail-area">
                    <div><img src="@if($data->icatch_thumbnail) {{ $data->icatch_thumbnail }} @endif" id="icatch-thumbnail" class="thumbnail"></div>
                    <div class="delete-btn btn icatch-btn"><a id="clear">削除</a></div>
                </div>
                @if($data->icatch)
                <input type="hidden" name="save_icatch" value="{{ $data->icatch }}">
                <input type="hidden" name="save_delete" id="save-delete" value="0">
                @endif
                <input type="hidden" id="max_filesize" value="{{ config('umekoset.max_filesize') }}">
                @foreach(config('umekoset.image_ex') as $key=>$ex)
                <input type="hidden" id="image_ex{{ $key }}" value="{{ $ex }}">
                @endforeach
            </dd>
            <dt>ページ名称</dt>
            <dd class="article_parts article_path">
                <input name="path" type="text" placeholder="このページの名称を入力します。半角英数字と「-（ハイフン）」「_（アンダーバー）」が使用できます。" value="{{ old('path', ($data->path ?? session('path'))) }}">
                <div><small>公開したページは/公開年/公開月/ページ名称 でアクセスします。</small></div>
                @error('path')<p class="err-msg">{{ $message }}</p>@enderror
            </dd>
            <dt>記事タイトル</dt>
            <dd class="article_parts article_title"><input name="post_title" type="text" value="{{ old('post_title', ($data->title ?? session('post_title'))) }}" placeholder="記事タイトルを入力">@error('post_title')<p class="err-msg">{{ $message }}</p>@enderror</dd>
        </dl>
        <div class="article_contents">
            <div id="trumbowyg-editor">{!! old('trumbowyg-editor', ($data->contents ?? session('trumbowyg-editor'))) !!}</div>
            @error('trumbowyg-editor')<p class="err-msg">{{ $message }}</p>@enderror
        </div>
        <dl>
            @if(!$categories->isEmpty())
            <dt>カテゴリー</dt>
            @foreach($categories as $key=>$cat)
            @if(($key+1)%3 == 1 || $key == 0) <dd class="article_parts category_flex"> @endif
            <div class="category_col"><label for="category_{{ $cat->id }}"><input type="checkbox" name="category[]" id="category_{{ $cat->id }}" value="{{ $cat->id }}" @if(in_array($cat->id, $relCategories)) checked @endif>{{ $cat->disp_name }}</label></div>
            @if(($key+1)%3 == 0) </dd> @endif
            @endforeach
            @if(($key+1)%3 != 0) </dd> @endif
            @endif
            <dt>SEO description</dt>
            <dd class="article_parts">
                <textarea name="seo_description">{{ old('seo_description', ($data->seo_description ?? session('seo_description'))) }}</textarea>
                <div><small>このページの説明文を記載します。HTMLタグと改行は使用できません。</small></div>
                @error('seo_description')<p class="err-msg">{{ $message }}</p>@enderror
            </dd>
        </dl>
        <input type="hidden" name="id" value="{{ $id }}">
        <div class="submit-btn"><button class="btn submit" type="submit">@if(empty($id)) 投稿 @else 更新@endif</button></div>
        </form>
    </div>
</div>
@endsection

