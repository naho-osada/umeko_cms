<?php
/**
 * HtmlController
 * サイトマップをベースに、全ページのHtmlファイルを出力する機能
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Route;
use App\Library\CommonPublic;
use App\Models\Category;
use App\Models\RelatedCategory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use DateTime;
use ZipArchive;

class HtmlController extends Controller
{
    protected $_HTTP_OK =200;

    public function __construct(Route $route)
    {
        $this->middleware('auth');
    }

    /**
     * index
     * トップ画面
     * サイトマップに登録されているページを全て表示する
     * @access public
     */
    public function index()
    {
        $this->checkAdmin();
        setcookie('downloadok', '', time()-3600, '/admin/html');

        $db = new Article();
        $offset = 0;
        $limit = 1000;
        $pages = [];
        // トップページを追加
        $topData = $db->getRecentUpdArticle();
        $cnt = 1;
        $date = new DateTime($topData->updated_at);
        $pages[$cnt] = (object)[
            'url' => '/',
            'title' => 'トップページ',
            'update' => $date->format('Y/m/d H:i:s')
        ];
        $cnt++;
        // カテゴリー一覧ページを追加
        $catDb = new Category();
        $category = $catDb->getListPublish();
        $relCat = new RelatedCategory;
        foreach($category as $cat) {
            // カテゴリの中の最新記事を日付に使う
            $catArticle = $relCat->getRelCatNameArticle($cat->category_name);
            $updated_at = empty($catArticle) ? config('umekoset.default_published_time') : $catArticle[0]->updated_at;
            $url = url('/category/' . $cat->category_name . '/');
            $date = new DateTime($updated_at);
            $pages[$cnt] = (object)[
                'url' => $url,
                'title' => $cat->disp_name . '一覧',
                'update' => $date->format('Y/m/d H:i:s')
            ];
            $cnt++;
        }
        // 各記事の情報を追加
        $common = new CommonPublic();
        while($data = $db->getAllData($offset, $limit)) {
            $siteData = $common->setUrl($data);
            foreach($siteData as $d) {
                $date = new DateTime($d->updated_at);
                $pages[$cnt] = (object)[
                    'url' => $d->url,
                    'title' => $d->title,
                    'update' => $date->format('Y/m/d H:i:s')
                ];
                $cnt++;
            }
            $offset = $limit + $offset;
        }
        $pages = (object)$pages;

        return view('admin.html.index', compact('pages', 'cnt'));
    }

    /**
     * HTML生成
     * @access public
     */
    public function make(request $request) {
        $this->checkAdmin();
        $db = new Article();
        $offset = 0;
        $limit = 1000;
        $pages = [];

        // 自由度を残すため、ここでは文字数だけチェックする
        $domain = $request->domain;
        $request->validate(['domain' => ['max:255']]);
        // 指定がないときは設定ファイルのドメインを指定
        if($domain === '' || is_null($domain)) {
            $domain = config('umekoset.html_domain');
        }

        // 作成前に以前のものを全削除する
        Storage::deleteDirectory('html-maker');
        Storage::delete('html.zip');

        // 全ページのURLを取得
        $cnt = 0;
        $urls = [];
        $urls[$cnt] = asset('/'); //トップページ
        $htmlUrls = [];
        $htmlUrls[$cnt]['url'] = '/index.html';
        $htmlUrls[$cnt]['type'] = 'html';
        $cnt++;

        // カテゴリー一覧ページ
        //$urls → curlするページ、ドメインはシステムのまま
        $catDb = new Category();
        $category = $catDb->getListPublish();
        $relCat = new RelatedCategory;
        foreach($category as $cat) {
            // カテゴリの中の最新記事を日付に使う
            // Pagerなし、全件表示にする
            $catArticle = $relCat->getRelCatNameArticle($cat->category_name);
            $urls[$cnt] = url('/category/' . $cat->category_name . '/?html=1');
            $htmlUrls[$cnt]['url'] = '/category/' . $cat->category_name . '.html';
            $htmlUrls[$cnt]['type'] = 'html';
            $cnt++;
        }
        // 各記事の情報を追加
        $common = new CommonPublic();
        $years = [];
        $yms = [];
        while($data = $db->getAllData($offset, $limit)) {
            $siteData = $common->setUrl($data);
            foreach($siteData as $d) {
                $urls[$cnt] = $d->url;
                $url = str_replace(url(''), '', $d->url);
                $htmlUrls[$cnt]['url'] = $url . '.html';
                $htmlUrls[$cnt]['type'] = 'html';
                $paths = explode('/', $d->url);
                array_pop($paths);
                $m = array_pop($paths);// 月
                $y = array_pop($paths);// 年

                if(in_array($y, $years) === false) {
                    $years[] = $y;
                }
                if(in_array($y . '/' . $m, $yms) === false) {
                    $yms[] = $y . '/' . $m;
                }

                $cnt++;
            }
            $offset = $limit + $offset;
        }
        // 年月アーカイブページを作る
        // Pagerなし、全件表示にする ここから
        foreach($years as $year) {
            $urls[$cnt] = url('') . '/date/' . $year . '/?html=1';
            $htmlUrls[$cnt]['url'] = '/date/' .$year . '.html';
            $htmlUrls[$cnt]['type'] = 'html';
            $cnt++;
        }
        foreach($yms as $ym) {
            $urls[$cnt] = url('') . '/date/' . $ym . '?html=1';
            $htmlUrls[$cnt]['url'] = '/date/' . $ym . '.html';
            $htmlUrls[$cnt]['type'] = 'html';
            $cnt++;
        }

        // 404ページ（わざと404にする）
        $urls[$cnt] = url('') . '/404htmlmaker';
        $htmlUrls[$cnt]['url'] = '/404.html';
        $htmlUrls[$cnt]['type'] = 'html';
        $cnt++;

        // CSS
        $cssData = glob(public_path() . ('/css/*'));
        foreach($cssData as $css) {
            if(is_dir($css)) continue;
            $path = str_replace(public_path(), '', $css);
            $urls[$cnt] = url('') . $path;
            $htmlUrls[$cnt]['url'] = $path;
            $htmlUrls[$cnt]['type'] = 'css';
            $cnt++;
        }

        // js
        $jsData = glob(public_path() . ('/js/*'));
        foreach($jsData as $js) {
            if(is_dir($js)) continue;
            $path = str_replace(public_path(), '', $js);
            $urls[$cnt] = url('') . $path;
            $htmlUrls[$cnt]['url'] = $path;
            $htmlUrls[$cnt]['type'] = 'js';
            $cnt++;
        }

        // sitemap
        $urls[$cnt] = url('') . '/storage/sitemap.xml';
        $htmlUrls[$cnt]['url'] = '/sitemap.xml';
        $htmlUrls[$cnt]['type'] = 'xml';
        $cnt++;

        // HTMLページ生成
        foreach($urls as $key=>$url) {
            $htmlData = $this->setHtml($url, $domain);
            Storage::disk('local')->put('html-maker' . $htmlUrls[$key]['url'], $htmlData);
        }

        // 画像などをコピー
        $imageData = Storage::disk('local')->allFiles('/public');
        foreach($imageData as $img) {
            if(strpos($img, '.git') !== false || strpos($img, 'xml') !== false) continue;
            $path = str_replace('public', '', $img);
            Storage::disk('local')->put('html-maker' . $path, Storage::disk('local')->get($img));
        }

        // zip圧縮
        $zip = new ZipArchive();
        $res = $zip->open(Storage::disk('local')->path('html.zip'), ZipArchive::CREATE);
        $files = glob(Storage::disk('local')->path('html-maker') . '/*');
        $zip = $this->setZip($zip, $files);
        $zip->close();
        ob_end_clean();

        if(!app()->runningUnitTests()) {
            // 通常 テストの時はダウンロードさせない
            // ダウンロードさせる
            $mimeType = Storage::mimeType(Storage::disk('local')->path('html.zip'));
            $headers = [
                ['Content-Type' => $mimeType,
                'Content-Length' => filesize(Storage::disk('local')->path('html.zip'))]
                ];
            setcookie('downloadok', 'true', time()+3600);
            return Storage::disk('local')->download('html.zip', 'html.zip', $headers);
        }
    }

    /**
     * setZip
     * zip圧縮する
     * @access private
     * @param $zip
     * @param $files
     * @return $zip
     */
    private function setZip($zip, $files) {
        $basePath = Storage::disk('local')->path('html-maker');
        foreach ($files as $file) {
            if(is_dir($file)) {
                $zip->addEmptyDir(str_replace($basePath . '/', '', $file));
                $this->setZip($zip, glob($file . '/*'));
            } else {
                $addFile = str_replace($basePath . '/', '', $file);
                $zip->addFile($file, $addFile);
            }
        }
        return $zip;
    }

    /**
     * setHtml
     * HTML内容を取得し、ドメインを変換して返す
     * @param $url 情報を取得するURL
     * @param $domain 変換するドメイン名（http://含む）
     */
    private function setHtml($url, $domain) {
        $response = Http::get($url);
        if($response->status() != 200) {
            if(strpos($url, '404htmlmaker') === false) {
                return $response->status();
            }
        }
        $html = str_replace(url(''), $domain, $response->body());
        preg_match_all('|<a href=\"' . $domain . '(.*?)\".*?>(.*?)</a>|s', $html, $cData, PREG_PATTERN_ORDER);
        preg_match_all('|(.*?)storage/uploads(.*?)|s', $html, $imgData, PREG_PATTERN_ORDER);
        $links = [];
        foreach($cData[1] as $data) {
            $links[] = $data;
        }
        $links = array_unique($links);
        foreach($links as $link) {
            if($link === '') {
                continue;
            } else {
                $html = str_replace($link, $link . '.html', $html);
            }
        }

        // 画像などの保存場所置換
        $html = str_replace('storage/uploads', 'uploads', $html);
        return $html;
    }

    /**
     * checkAdmin
     * 権限チェック
     * 管理者以外のときはアクセス制限がかかる
     * @access private
     * @return true or redirect
     */
    private function checkAdmin()
    {
        // 管理者は何もしない
        if(Auth::user()->auth == 1) {
            return true;
        } else {
            abort(redirect('/admin/top'));
        }
        return true;
    }
}
