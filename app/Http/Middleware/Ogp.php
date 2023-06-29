<?php
/**
 * OGPタグを生成する
 */
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\View\Factory;
use Illuminate\Support\Facades\Route;
use App\Models\Article;
use App\Models\Category;
use App\Library\CommonPublic;
use Carbon\Carbon;
use App\Models\RelatedCategory;

class Ogp
{
    public function __construct(Factory $viewFactory)
    {
        $this->viewFactory = $viewFactory;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $common = new CommonPublic();
        $ogp = [];
        $siteName = config('umekoset.site_name');
        $ogp['site_name'] = $siteName;
        // アクション名を判定
        $actions = explode("@", Route::currentRouteAction());
        $db = new Article();
        if($actions[1] == 'index') {
            // TOP
            // 最新記事の更新日時を取得する
            $upd = $db->getRecentUpdArticle();
            $update = '';
            if(isset($upd->updated_at)) {
                $dateobj = new Carbon($upd->updated_at);
                $update = $dateobj->format('Y-m-dTH:i:s');
            }
            $ogp['title'] = $siteName;
            $ogp['description'] = config('umekoset.default_description');
            $ogp['published_time'] = config('umekoset.default_published_time');
            $ogp['modified_time'] = ($update == '') ? config('umekoset.default_published_time') : $update;
            $ogp['image'] = asset(config('umekoset.top_image'));
            $ogp['url'] = url('/');
        } else if($actions[1] == 'article') {
            // 記事
            // routeで簡易バリデーションをかけているので、そのまま検索条件に入れてOK
            $url = str_replace(config('app.url'), '/', $request->url());
            $uri = explode('/', $url);
            if(count($uri) != 4) return abort('404');
            $year = $uri[1];
            $month = $uri[2];
            $path = $uri[3];
            $search = [];
            $startDate = $year . '-' . $month  . '-01 00:00:00';
            $endMonth = intval($month) + 1;
            if($endMonth == 13) {
                $endMonth = 1;
                $year = $year + 1;
            }
            $endDate = $year . '-' . sprintf('%02d', $endMonth)  . '-01 00:00:00';
            $search['start_date'] = $startDate;
            $search['end_date'] = $endDate;
            $search['path'] = $path;
            // 記事の内容の取得
            $article = $db->searchArticle($search);
            if(empty($article)) return abort('404');

            $article = $common->setDefaultData($article, 'large');

            $article = $article[0];
            if(!isset($article->icatch_thumbnail)) {
                $article->icatch_thumbnail = asset(config('umekoset.noimage'));
            }
            $ogp['title'] = $article->title . config('umekoset.separate') . $siteName;
            $ogp['description'] = $article->seo_description;
            $ogp['published_time'] = $article->publish_at;
            $ogp['modified_time'] = $article->updated_at;
            $ogp['image'] = isset($article->icatch_thumbnail) ? $article->icatch_thumbnail : asset(config('umekoset.noimage'));
            $ogp['url'] = url($article->url);

            // OGPタグで記事の基本情報は取得するのでリクエストに入れる
            $request['article'] = $article;
        } else if($actions[1] == 'category') {
            // カテゴリ一覧
            $url = str_replace(config('app.url'), '/', $request->url());
            $uri = explode('/', $url);
            if($uri[2] == '' || $uri[1] != 'category') return abort('404');
            $name = urldecode($uri[2]);
            $catDb = new Category();
            $category = $catDb->getCategoryName($name);
            if(empty($category[0])) return abort('404');
            $category = $category[0];
            $relCat = new RelatedCategory;
            $catArticle = $relCat->getRelCatNameArticle($name);
            $updateCatArtDate = empty($catArticle) ? config('umekoset.default_published_time') : $catArticle[0]->updated_at;

            $ogp['title'] = $category->category_name . ' の記事' . config('umekoset.separate') . $siteName;
            $ogp['description'] = 'カテゴリー名「' . $category->category_name . '」の一覧です。';
            $ogp['published_time'] = config('umekoset.default_published_time');
            $ogp['modified_time'] = $updateCatArtDate;
            $ogp['image'] = asset(config('umekoset.top_image'));
            $ogp['url'] = url($request->getRequestUri());

            // OGPタグで記事の基本情報は取得するのでリクエストに入れる
            $request['category'] = $category;
        } else if($actions[1] == 'date') {
            $url = str_replace(config('app.url'), '/', $request->url());
            $uri = explode('/', $url);
            if($uri[2] == '' || $uri[1] != 'date') return abort('404');
            $year = intval($uri[2]);
            $month = isset($uri[3]) ? $uri[3] : '';
            if(strlen($year) != 4) return abort('404');
            if($month != '') {
                if(strlen($month) > 2) return abort('404');
                if(intval($month) < 0 || intval($month) > 12) return abort('404');
            }
            $search['start_date'] = $year . '-' . (empty($month) ? '01' : sprintf('%02d', $month)) . '-01 00:00:00';
            $search['end_date'] = (empty($month) ? $year+1 : (($month == 12) ? $year+1 : $year)) . '-' . (empty($month) ? '01' : sprintf('%02d', (($month == 12) ? 1 : $month+1))) . '-01 00:00:00';
            // 開始年月が当月で来た場合、終了日は本日の次の日までとする
            if(date('Y', strtotime($search['start_date'])) == date('Y') && date('m', strtotime($search['start_date'])) == date('m')) {
                $search['end_date'] = date('Y-m-d 00:00:00', strtotime('+1day'));
            }

            $db = new Article();
            $article = $db->searchArticleDate($search);
            $article = $common->setDefaultData($article, 'small');

            $ogp['title'] = $year . '年' . (empty($month) ? '' : intval($month) . '月') . 'の記事' . config('umekoset.separate') . $siteName;
            $ogp['description'] = $year . '年' . (empty($month) ? '' : intval($month) . '月') . 'の記事一覧です。';
            $ogp['published_time'] = config('umekoset.default_published_time');
            $ogp['modified_time'] = config('umekoset.default_published_time');
            $ogp['image'] = asset(config('umekoset.top_image'));
            $ogp['url'] = url($request->getRequestUri());

            $request['articles'] = $article;
        } else if($actions[1] == 'preview') {
            // プレビュー機能用
            $ogp['title'] = $request->post_title . config('umekoset.separate') . $siteName;
            $ogp['description'] = $request->seo_description;
            $ogp['published_time'] = $request->open_year . '-' . $request->open_month . '-' . $request->open_day . ' ' .$request->open_hour . ':' . $request->open_min;
            $ogp['modified_time'] = date('Y-m-d H:i:s');
            $ogp['image'] = '';
            $ogp['url'] = '';
        }
        $this->viewFactory->share('ogp', $ogp);

        return $next($request);
    }
}
