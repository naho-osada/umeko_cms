<div class="menu">
        <section>
            <h3>制作者プロフィール</h3>
            <div class="sidebar_profile"><img src="https://engineer-lady.com/wp/wp-content/uploads/2021/07/Fujiglass-300x300.png" alt="制作者"></div>
            <div class="sidebar_profile_text">通称「エンジニア婦人」です。<br>
            梅子の開発者。<br>
            PHPを得意としています。<br>
            その他、<a href="https://engineer-lady.com/">エンジニア婦人ノート</a>の運営、保守をしています。<br>
            <span class="text-right"><a href="https://engineer-lady.com/about/">詳細はこちら</a></span>
            </div>
        </section>

        @if(!$sidebarCategory->isEmpty())<section class="sidebar_category">
            <h3>カテゴリー</h3>
            <ul>
                @foreach ($sidebarCategory as $data)
                <li>
                    <a href="{{ url('/category/' . $data->category_name . '/') }}">
                        {{ $data->disp_name }}（@if($data->article_cnt == null) 0 @else {{ $data->article_cnt }} @endif）
                    </a>
                </li>
                @endforeach
            </ul>
        </section>@endif
        @if($newArticle)<section class="sidebar_newarticle">
            <h3>最近更新された記事</h3>
            <ul>
                @foreach ($newArticle as $data)
                <li class="sidebar-article-list">
                    <div class="sidebar-list-icatch">
                    <a href="{{ $data->url }}">
                    @if(isset($data->icatch_thumbnail))
                    <img src="{{ $data->icatch_thumbnail }}" alt="{{ $data->title }}の画像">
                    @else
                    <img src="{{ asset(config('umekoset.noimage')) }}" alt="NoImage">
                    @endif
                    </a>
                    </div>
                    <h4><a href="{{ $data->url }}">{{ $data->title }}</a></h4>
                </li>
                @endforeach
            </ul>
        </section>@endif
        @if($archiveCnt)
        <section class="sidebar-archive-list">
            <h3>アーカイブ</h3>
            <ul>
                @foreach ($archiveCnt as $key=>$cnt)
                <?php
                    $year = mb_substr($key, 0, 4);
                    $month = sprintf('%d', mb_substr($key, 4));
                ?>
                <li>
                    @if($cnt === 0) 
                    {{ $year }}年{{ $month }}月（&nbsp;{{ $cnt }}&nbsp;）
                    @else
                    <a href="{{ asset('/date/' . $year . '/' . $month) }}">{{ $year }}年{{ $month }}月（&nbsp;{{ $cnt }}&nbsp;）</a>
                    @endif
                </li>
                @endforeach
            </ul>
        </section>
        @endif
</div>
