<?php
/**
 * CaetegoryController
 * 管理画面 カテゴリー
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\RelatedCategory;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Rules\CategorySort;
use App\Rules\HalfStringSymbol;

class CategoryController extends Controller
{
    public function __construct(Route $route)
    {
        $this->middleware('auth');
        $actions = explode('@', $route->getActionName());
        $this->action = $actions[1];
    }

    /**
     * checkAdmin
     * 権限チェック
     * 管理者以外のときはアクセス制限がかかる
     * @access private
     * @param $id default empty
     * @return true or redirect
     */
    private function checkAdmin($id = '')
    {
        // 管理者は何もしない
        if(Auth::user()->auth == 1) return true;

        // 管理者以外は削除ページを無効
        if($this->action == 'deleteConfirm'
        || $this->action == 'deleteProc') {
            abort(redirect('/home'));
        }
        return true;
    }

    /**
     * index
     * カテゴリ一覧画面
     * @access public
     * @param Request $request
     */
    public function index(Request $request)
    {
        $db = new Category();
        $category = $db->getList();

        return view('admin.category.index', compact('category'));
    }

    /**
     * deleteConfirm
     * カテゴリの削除確認
     * @access public
     * @param Request $request
     */
    public function deleteConfirm(Request $request)
    {
        $this->checkAdmin();
        $id = $request->id;
        $data = '';
        if(empty($id)) return redirect('/admin/category');
        $request->validate(['id' => ['required','integer']]);
        // カテゴリー内容の取得
        $db = new Category();
        $search = $this->setDefaultParams($id);
        $catData = $db->getCategory($search);
        if(empty($catData)) return redirect('/admin/category');
        $catData = $catData[0];

        // このカテゴリを使用している記事を探す
        $dbrel = new RelatedCategory();
        $relCat = $dbrel->getRelCatArticle($id);
        foreach($relCat as $key=>$data) {
            // 本文のタグを消して表示する
            $str = strip_tags(str_replace('<br>', "\n", str_replace('</p>', "\n", $data->contents)));
            if(mb_strlen($str) > 100) {
                $str = mb_substr($str, 0, 100);
                $str .= ' ...';
            }
            $str = str_replace("\n", '<br>', $str);
            $relCat[$key]->contents = $str;
        }

        return view('admin.category.delete-confirm', compact('id', 'catData', 'relCat'));
    }

    /**
     * deleteProc
     * カテゴリの削除処理
     * @access public
     * @param Request $request
     */
    public function deleteProc(Request $request)
    {
        $this->checkAdmin();
        $id = $request->id;
        if(empty($id)) return redirect('/admin/category');
        $request->validate(['id' => ['required','integer']]);
        $db = new Category();
        // カテゴリの存在確認
        $search = $this->setDefaultParams($id);
        $data = $db->getCategory($search);
        if(!$data) return redirect('/admin/category');
        // カテゴリの削除
        $result = $db->deleteCategory($id);
        // 削除したカテゴリの記事に紐づく情報を削除
        $dbRel = new RelatedCategory();
        $relIds = $dbRel->getRelCats($id);
        foreach($relIds as $relData) {
            $dbRel->deleteRelCategory($relData->id);
        }

        // 記事一覧へリダイレクトする
        if($result == 1) {
            session()->flash('flashmessage', 'カテゴリーを削除しました。');
            return redirect('/admin/category');
        } else {
            session()->flash('flashmessage', 'カテゴリーの削除に失敗しました。');
            return redirect('/admin/category');
        }
    }

    /**
     * edit
     * カテゴリ投稿&編集画面
     * @access public
     * @param Request $request
     */
    public function edit(Request $request)
    {
        $id = $request->id;
        $data = '';
        $db = new Category();
        if(!empty($id)) {
            $request->validate(['id' => ['required','integer']]);
            $search = $this->setDefaultParams($id);
            // 記事の内容の取得
            $data = $db->getCategory($search);
            if(empty($data)) return redirect('/admin/category');
            $data = $data[0];
        }
        // ソートのセレクトボックス生成用
        // 現在登録されているカテゴリ数を元に算出
        // 何もない場合は1のみ
        $sortNum = $db->getCategoryNum();
        if($sortNum == 0) $sortNum = 1;
        return view('admin.category.edit', compact('id', 'data', 'sortNum'));
    }

    /**
     * editProc
     * カテゴリ投稿&編集処理
     * @access public
     * @param Request $request
     */
    public function editProc(Request $request)
    {
        $id = $request->id;
        $now = Carbon::now();
        $db = new Category();
        if(!empty($id)) {
            $request->validate(['id' => ['required','integer']]);
            // 該当IDのものが更新可能か確認する
            $search = $this->setDefaultParams($id);
            $data = $db->getCategory($search);
            if(empty($data)) return redirect('/admin/category');
        }

        // カテゴリ表示名、カテゴリー名はHTMLタグ不許可
        $request['category_name'] = htmlspecialchars($request['category_name'], ENT_QUOTES, 'UTF-8');
        $request['disp_name'] = htmlspecialchars($request['disp_name'], ENT_QUOTES, 'UTF-8');

        $checkValid = [
            'category_name' => ['required', 'max:50', new HalfStringSymbol, 'unique:category,category_name,' . $id . ',id'],
            'disp_name' => ['required', 'max:50'],
        ];
        if($request['sort_no'] != '') {
            $checkValid['sort_no'] = ['integer', new CategorySort];
        }
        // Validate
        $request->validate($checkValid);

        // カテゴリーの登録、更新
        $updData = [];
        $updData['category_name'] = $request['category_name'];
        $updData['disp_name'] = $request['disp_name'];
        $updData['sort_no'] = $request['sort_no'];
        $updData['updated_user_id'] = Auth::user()->id;
        $updData['updated_at'] = $now;

        if(empty($id)) {
            // 登録
            $updData['user_id'] = Auth::user()->id;
            $updData['created_at'] = $now;
            $id = $db->addCategory($updData);

            if(!is_int($id)) {
                session()->flash('flashmessage', '投稿失敗しました。');
                session()->flash('category_name', $request['category_name']);
                session()->flash('disp_name', $request['disp_name']);
                session()->flash('sort_no', $request['sort_no']);
                return redirect('/admin/category/edit');
            } else {
                // 更新されたデータを取得する
                $search = $this->setDefaultParams($id);
                $data = $db->getCategory($search);
                if(empty($data)) {
                    session()->flash('flashmessage', '投稿失敗しました。');
                    session()->flash('category_name', $request['category_name']);
                    session()->flash('disp_name', $request['disp_name']);
                    session()->flash('sort_no', $request['sort_no']);
                    return redirect('/admin/category/edit');
                }
                session()->flash('flashmessage','カテゴリーを投稿しました。');
                return redirect('/admin/category/edit?id=' . $id);
            }
        } else {
            // 更新
            $updData['id'] = $id;
            $result = $db->updateCategory($updData);
            // editへリダイレクトする
            if($result == 1) {
                // 更新されたデータを取得する
                $search = $this->setDefaultParams($id);
                $data = $db->getCategory($search);
                if(empty($data)) return redirect('/home');
                session()->flash('flashmessage','カテゴリー情報を更新しました。');
                return redirect('/admin/category/edit?id=' . $id);
            } else {
                session()->flash('flashmessage', '更新失敗しました。');
                session()->flash('category_name', $request['category_name']);
                session()->flash('disp_name', $request['disp_name']);
                session()->flash('sort_no', $request['sort_no']);
                return redirect('/admin/category/edit?id=' . $id);
            }
        }
    }

    /**
     * setDefaultParams
     * ログインユーザーによるデフォルトの検索条件をセットする
     * @access private
     * @param $id
     * @return $search 検索条件
     */
    private function setDefaultParams($id = '')
    {
        $search = [];
        $search['id'] = $id;
        // 管理者ログイン以外は作成者が自分のもののみ
        if(Auth::user()->auth != 1) {
            $search['user_id'] = Auth::user()->id;
        }
        return $search;
    }
}
