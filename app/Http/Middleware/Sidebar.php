<?php
/**
 * 公開側共通
 * サイドバーで使うデータを設定してViewに出力する
 */
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\View\Factory;
use App\Models\Article;
use App\Models\Category;
use App\Library\CommonPublic;

class Sidebar
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

        // サイドバーの設定
        // 最近更新された記事
        $recentday = config('umekoset.sidebar_recent_day');
        $search = [];
        $search['recentday'] = $recentday;
        $articleDb = new Article();
        $newArticle = $articleDb->searchArticle($search);
        $newArticle = $common->setDefaultData($newArticle, 'small');
        $this->viewFactory->share('newArticle', $newArticle);

        // カテゴリー一覧
        $catDb = new Category();
        $category = $catDb->getListPublish();
        $this->viewFactory->share('sidebarCategory', $category);

        // 年月アーカイブ
        // 前年同月～今年分の2年分
        $search = [];
        $search['all'] = true;
        $prevDate = date('Y-01-01 00:00:00', strtotime('-1 year'));
        $search['start_date'] = $prevDate;
        $search['end_date'] = date('Y-m-d 00:00:00', strtotime('+1 day'));

        $prevYear = date('Y', strtotime('-1 year'));
        $archiveCnt = [];
        for($i=1; $i <= 12; $i++) {
            $archiveCnt[$prevYear . sprintf('%02d', $i)] = 0;
        }
        $nowYear = date('Y');
        $nowMonth = date('m');
        for($i=1; $i <= $nowMonth; $i++) {
            $archiveCnt[$nowYear . sprintf('%02d', $i)] = 0;
        }

        $data = $articleDb->searchArticleDate($search);
        foreach($data as $d) {
            $dates = explode(' ', $d->publish_at);
            $date = explode('-', $dates[0]);
            $year = $date[0];
            $month = sprintf('%02d', $date[1]);
            if(!isset($archiveCnt[$year . $month])) continue;
            $archiveCnt[$year . $month]++;
        }
        // 年月で0件が続いている場合は過去の連続するところを削除する
        // 0がなくなった時点で終了
        ksort($archiveCnt);
        foreach($archiveCnt as $key=>$data) {
            if($data == 0) {
                unset($archiveCnt[$key]);
            } else {
                break;
            }
        }
        krsort($archiveCnt);
        $this->viewFactory->share('archiveCnt', $archiveCnt);

        return $next($request);
    }
}
