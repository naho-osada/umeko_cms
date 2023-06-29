<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use App\Models\Article;
use App\Library\CommonPublic;
use App\Models\Category;
use App\Models\RelatedCategory;

class Sitemap
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);

        // サイトマップを生成する
        // リクエストの中に何が入ってるか？確認
        // もし今公開した記事の「更新」等が入っていたら、「更新」のときはサイトマップ更新、そうでない場合は何もしないようにしたい
        $a = '';
        // Articleの公開中データを5000件取得する（5000件ループにしておく？）
        // 初回、既定のurlsetを書く<urlset>
        // <url> <loc>にURL <lastmod>に更新日 をかく </url>
        // </urlset>
        // publicにsitemap.xmlとして保存
    }

    public function terminate($request, $response)
    {
        // 「更新」を押したら最後にサイトマップの生成をする
        $db = new Article();
        $offset = 0;
        $limit = 1000;
        $topData = $db->getRecentUpdArticle();
        $xmlset = '<?xml version="1.0" encoding="UTF-8"?>';
        $urlset = "<urlset xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd' xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>";
        $topPage = '<url>' . "\n". '<loc>' . asset('/') . '</loc>' . "\n" . '<lastmod>' . $topData->updated_at . '</lastmod>' . "\n" . '</url>';
        $sitemapFile = 'public/sitemap.xml';

        $common = new CommonPublic();
        Storage::put($sitemapFile, $xmlset);
        Storage::append($sitemapFile, $urlset);
        // トップページを追加
        Storage::append($sitemapFile, $topPage);
        // カテゴリー一覧ページを追加
        $catDb = new Category();
        $category = $catDb->getListPublish();
        $relCat = new RelatedCategory;
        $csvAry = [];
        foreach($category as $cat) {
            // カテゴリの中の最新記事を日付に使う
            $catArticle = $relCat->getRelCatNameArticle($cat->category_name);
            $updated_at = empty($catArticle) ? config('umekoset.default_published_time') : $catArticle[0]->updated_at;

            $url = url('/category/' . $cat->category_name . '/');
            $csvAry[] = '<url>' . "\n". '<loc>' . $url . '</loc>' . "\n" . '<lastmod>' . $updated_at . '</lastmod>' . "\n" . '</url>';
            if(count($csvAry) == $limit) {
                // カテゴリ件数が多く1000行になったらデータを書き込む
                Storage::append($sitemapFile, implode("\n", $csvAry));
                $csvAry = [];
            }
        }
        Storage::append($sitemapFile, implode("\n", $csvAry));

        // @foreach ($sidebarCategory as $data)
        // <li>
        //     <a href="{{ url('/category/' . $data->category_name . '/') }}">
        //         {{ $data->disp_name }}（@if($data->article_cnt == null) 0 @else {{ $data->article_cnt }} @endif）
        //     </a>
        // </li>
        // @endforeach

        // 次回ここから
        while($data = $db->getAllData($offset, $limit)) {
            $csvAry = [];
            $siteData = $common->setUrl($data);
            foreach($siteData as $d) {
                $csvAry[] = '<url>' . "\n". '<loc>' . $d->url . '</loc>' . "\n" . '<lastmod>' . $d->updated_at . '</lastmod>' . "\n" . '</url>';
            }
            $offset = $limit + $offset;
            Storage::append($sitemapFile, implode("\n", $csvAry));
        }
        Storage::append($sitemapFile, '</urlset>' . "\n");


        // 公開中データを全取得
    }
}
