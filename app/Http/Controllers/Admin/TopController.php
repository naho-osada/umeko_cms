<?php
/**
 * TopController
 * 管理画面 トップページ
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Route;

class TopController extends Controller
{
    public function __construct(Route $route)
    {
        $this->middleware('auth');
    }

    /**
     * index
     * トップ画面
     * @access public
     */
    public function index()
    {
        $search = [];
        // 管理者ログイン以外は編集権限が「管理者+作成者」のもののみとする
        if(Auth::user()->auth != 1) {
            $search['article_auth'] = config('umekoset.article_auth_creator');
            $search['user_id'] = Auth::user()->id;
        }
        $db = new Article();
        $article = $db->getRecentList($search);
        foreach($article as $key=>$data) {
            // 本文のタグを消して表示する
            $str = strip_tags(str_replace('<br>', "\n", str_replace('</p>', "\n", $data->contents)));
            if(mb_strlen($str) > 100) {
                $str = mb_substr($str, 0, 100);
                $str .= ' ...';
            }
            $str = str_replace("\n", '<br>', $str);
            $article[$key]->contents = $str;
        }
        $date = Carbon::now()->format('Y年m月j日 H時i分');

        return view('admin.top', compact('article', 'date'));
    }
}
