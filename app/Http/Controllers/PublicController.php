<?php
/**
 * PublicController
 * 指定のパラメータから公開中ページを検索して表示する
 */

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\RelatedCategory;
use App\Library\CommonPublic;

class PublicController extends Controller
{
    /**
     * index
     * トップページ
     * 最近更新した記事を動的表示
     * @access public
     */
    public function index()
    {
        $db = new Article();
        $search = [];
        $article = $db->getPublishList();
        $relCatDb = new RelatedCategory();
        foreach($article as $key=>$data) {
            // 本文のタグを消して表示する
            $str = strip_tags(str_replace('<br>', "\n", str_replace('</p>', "\n", $data->contents)));
            if(mb_strlen($str) > 200) {
                $str = mb_substr($str, 0, 200);
                $str .= ' ...';
            }
            $str = str_replace("\n", '<br>', $str);
            $article[$key]->contents = $str;

            // アイキャッチ画像の設定
            $article[$key]->icatch_thumbnail = '';
            if($data->icatch) {
                $data->icatch_thumbnail = asset('storage/uploads/image/' . $data->icatch_y . '/' . $data->icatch_m . '/small/' . $data->icatch_file);
            }

            // 公開URLの設定
            $dateAry = explode(' ', $data->publish_at);
            $date = explode('-', $dateAry[0]);
            $data->url = asset('/' . $date[0] . '/' . $date[1] . '/' . $data->path);

            // カテゴリ情報の取得
            $relCategories = [];
            $relData = $relCatDb->getCategories($data->id);
            if(!empty($relData)) {
                foreach($relData as $reld) {
                    $relCategories[$key][$reld->category_id]['url'] = asset('/category/' . $reld->category_name);
                    $relCategories[$key][$reld->category_id]['name'] = $reld->disp_name;
                }
            }
        }

        return view('public.index', compact('article', 'relCategories'));
    }

    /**
     * article
     * 記事の表示
     * @access public
     * @param Request $request
     * @param $year 年
     * @param $month 月
     * @param $path 任意文字列
     */
    public function article(Request $request, $year, $month, $path)
    {
        // middlewareで取得済み
        $article = $request->article;
        $common = new CommonPublic();
        // 記事内容に目次を付ける h2～h4まで自動生成
        $article->contents = $common->setTableOfContents($article->contents);

        $db = new Article();

        // カテゴリ情報の取得
        $relCatDb = new RelatedCategory();
        $relData = $relCatDb->getCategories($article->id);
        $relCategories = [];
        $relCategoryIds = [];
        if(!empty($relData)) {
            foreach($relData as $reld) {
                $relCategories[$reld->category_id]['url'] = asset('/category/' . $reld->category_name);
                $relCategories[$reld->category_id]['name'] = $reld->disp_name;
                $relCategoryIds[] = $reld->category_id;
            }
        }

        // pager設定
        // 全件取得してforeachで回す
        // 該当IDの前後のデータを取る
        $pData = $db->searchArticlePager();
        $pager = [];
        foreach($pData as $key=>$page) {
            if($page->id != $article->id) continue;
            $pager['before'] = ($key-1 > 0) ? $pData[$key-1] : '';
            $pager['after'] = (isset($pData[$key+1])) ? $pData[$key+1] : '';
            break;
        }
        // アイキャッチ画像とURLの生成
        $pager = $common->setDefaultData($pager, 'small');

        // 関連記事の取得
        $relArticles = $relCatDb->getRelCatArticles($relCategoryIds, $article->id);
        // アイキャッチ画像とURLの生成
        $relArticles = $common->setDefaultData($relArticles, 'small');

        return view('public.article', compact('article', 'relCategories', 'pager', 'relArticles'));
    }

    /**
     * category
     * カテゴリ一覧ページ
     * @access public
     * @param $name カテゴリ名
     */
    public function category(Request $request, $name)
    {
        // HTML生成用の項目
        if(isset($request->html)) {
            $htmlFlag = $request->html;
        } else {
            $htmlFlag = false;
        }
        // middlewareで取得済み
        $category = $request->category;
        $relCatDb = new RelatedCategory();
        // 該当カテゴリ記事の取得
        $relArticles = $relCatDb->getRelCatNameArticle($name, $htmlFlag);
        // アイキャッチ画像とURLの生成
        $common = new CommonPublic();
        $relArticles = $common->setDefaultData($relArticles, 'small');

        $relCategories = [];
        foreach($relArticles as $key=>$data) {
            // カテゴリ情報の取得
            $relData = $relCatDb->getCategories($data->id);
            if(!empty($relData)) {
                foreach($relData as $reld) {
                    $relCategories[$key][$reld->category_id]['url'] = asset('/category/' . $reld->category_name);
                    $relCategories[$key][$reld->category_id]['name'] = $reld->disp_name;
                }
            }
        }

        return view('public.category', compact('category', 'relCategories', 'relArticles', 'htmlFlag'));
    }

    /**
     * date
     * 日付一覧
     * @param $request
     * @param $year 年
     * @param $month 月 空のときもある
     */
    public function date(Request $request, $year, $month='')
    {
        // HTML生成用の項目
        if(isset($request->html)) {
            $htmlFlag = $request->html;
        } else {
            $htmlFlag = false;
        }
        $relCategories = [];
        $search = $request->search;
        // htmlフラグがあるときは全件取得する
        if($htmlFlag) {
            $search['all'] = true;
            $db = new Article();
            $articles = $db->searchArticleDate($search);
            $common = new CommonPublic();
            $articles = $common->setDefaultData($articles, 'small');
        } else {
            $articles = $request->articles;
        }
        $relCatDb = new RelatedCategory();
        foreach($articles as $key=>$data) {
            // カテゴリ情報の取得
            $relData = $relCatDb->getCategories($data->id);
            if(!empty($relData)) {
                foreach($relData as $reld) {
                    $relCategories[$key][$reld->category_id]['url'] = asset('/category/' . $reld->category_name);
                    $relCategories[$key][$reld->category_id]['name'] = $reld->disp_name;
                }
            }
        }
        $dispDate = $year . '年' .(empty($month) ? '' : intval($month) . '月');
        return view('public.date', compact('dispDate', 'search', 'articles', 'relCategories', 'htmlFlag'));
    }
}
