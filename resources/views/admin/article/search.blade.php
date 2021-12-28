<div class="search-box">
    <div class="search-title">記事を検索</div>
        <form method="get" action="{{ url('/admin/article') }}">
        <table class="search-col">
            <tr>
                <th>ステータス</th>
                <td>
                    <ul class="status-search">
                        <li><input type="radio" name="status" id="status_all" value="" @if($search['status'] == '') checked @endif><label for="status_all">すべて</label>
                        @foreach(config('umekoset.status') as $key=>$name)
                        <li><input type="radio" name="status" id="status{{ $key }}"value="{{ $key }}" @if($search['status'] == $key) checked @endif><label for="status{{$key}}">{{ $name }}</label>
                        @endforeach
                    </ul>
                </td>
            </tr>
        </table>
        <div class="search-btn"><input type="submit" value="検索" class="btn"></div>
        </form>
    </div>
</div>