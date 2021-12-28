<?php
/**
 * UserController
 * 管理画面 ユーザー
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Users;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Rules\HalfString;
use App\Rules\AuthValue;
use Illuminate\Routing\Route;

class UserController extends Controller
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

        // 管理者以外は編集ページ以外を無効
        if($this->action == 'index'
        || $this->action == 'add'
        || $this->action == 'addProc'
        || $this->action == 'deleteConfirm'
        || $this->action == 'deleteProc') {
            abort(redirect('/admin/top'));
        } else if($this->action == 'edit' || $this->action == 'editProc') {
            // 編集画面、編集実行画面でも自分以外はアクセスできない
            if($id != Auth::user()->id) abort(redirect('/admin/top'));
        }
        return true;
    }

    /**
     * checkId
     * 処理をする前にIDの確認をする
     * 数値、nullチェック
     * @access private
     * @param $id
     * @return true or redirect
     */
    private function checkId($id)
    {
        if($id == null) return abort(redirect('/admin/top'));
        if(!preg_match('/^[0-9]+$/', $id)) return abort(redirect('/admin/top'));
        return true;
    }

    /**
     * index
     * ユーザー一覧画面
     * @access public
     */
    public function index()
    {
        $this->checkAdmin();
        $db = new Users();
        $users = $db->getList();

        return view('admin.user.index', compact('users'));
    }

    /**
     * add
     * ユーザー情報追加画面
     * @access public
     */
    public function add()
    {
        $this->checkAdmin();
        return view('admin.user.add');
    }

    /**
     * addProc
     * ユーザー情報登録処理
     * @access public
     * @param Request $request
     */
    public function addProc(Request $request)
    {
        $this->checkAdmin();

        // ユーザー名はHTMLタグ不許可
        $request['user_name'] = htmlspecialchars($request['user_name'], ENT_QUOTES, 'UTF-8');
        $request['email'] = htmlspecialchars($request['email'], ENT_QUOTES, 'UTF-8');

        $addData['user_name'] = $request->user_name;
        $addData['email'] = $request->email;
        $addData['password'] = Hash::make($request->password);
        $addData['auth'] = $request->auth;
        $request->validate([
            'user_name' => ['required', 'max:255'],
            'email'     => ['required', 'email:rfc,dns,strict,spoof', 'max:255', 'unique:users'],
            'password'  => ['required', 'string', new HalfString, 'max:255'],
            'auth'      => ['required', 'integer', new AuthValue],
        ]);

        // 登録
        $now = Carbon::now();
        $addData = [];
        $addData['user_name'] = $request->user_name;
        $addData['email'] = $request->email;
        $addData['password'] = Hash::make($request->password);
        $addData['auth'] = $request->auth;
        $addData['created_at'] = $now;
        $addData['updated_at'] = $now;
        $db = new Users();
        $id = $db->addUser($addData);

        if(!is_int($id)) {
            session()->flash('flashmessage', '登録失敗しました。');
            return redirect('/admin/user/add');
        } else {
            // 更新されたデータを取得する
            $db = new Users();
            $data = $db->getUser($id);
            if(empty($data)) {
                session()->flash('flashmessage', '登録失敗しました。');
                return redirect('/admin/user/add');
            }
            Auth::user()->user_name = $data->user_name;
            session()->flash('flashmessage','ユーザー情報を登録しました。');
            return redirect('/admin/user/edit?id=' . $id);
        }
    }

    /**
     * edit
     * ユーザー情報編集画面
     * @access public
     * @param Request $request
     */
    public function edit(Request $request)
    {
        $id = $request->id;
        $this->checkId($id);
        $this->checkAdmin($id);
        // 更新用データが存在することを確認
        $db = new Users();
        $data = $db->getUser($id);
        if(!$data) return redirect('/admin/top');

        $request->validate(['id' => ['required','integer']]);

        $data->auth = config('umekoset.auth.' . $data->auth);
        return view('admin.user.edit', compact('data'));
    }

    /**
     * editProc
     * ユーザー情報更新処理
     * @access public
     * @param Request $request
     */
    public function editProc(Request $request)
    {
        $id = $request['id'];
        $this->checkId($id);
        $this->checkAdmin($id);

        // 更新用データが存在することを確認
        $db = new Users();
        $data = $db->getUser($id);
        if(!$data) return redirect('/admin/top');

        // ユーザー名はHTMLタグ不許可
        $request['user_name'] = htmlspecialchars($request['user_name'], ENT_QUOTES, 'UTF-8');
        $request['email'] = htmlspecialchars($request['email'], ENT_QUOTES, 'UTF-8');

        // Validate
        // メールアドレスチェック
        $mailValide = ['required', 'email:rfc,dns,strict,spoof', 'max:255'];
        // 現在登録されている情報と異なる場合はユニークチェックを追加
        if($request->email != $data->email) {
            $mailValide[] = 'unique:users';
        }
        $checkValide = [
            'id'        => ['required', 'integer'],
            'user_name' => ['required', 'max:255'],
            'email'     => $mailValide,
        ];
        // パスワードは入力があったときのみチェックする
        if($request->password != '') $checkValide['password'] = ['string', new HalfString, 'max:255'];
        $request->validate($checkValide);

        // 更新
        $updData = [];
        $updData['id'] = $request->id;
        $updData['user_name'] = $request->user_name;
        $updData['email'] = $request->email;
        if(!empty($request->password)) $updData['password'] = Hash::make($request->password);
        $updData['updated_at'] = Carbon::now();
        $result = $db->updateUser($updData);

        if($result == 1) {
            // 更新されたデータを取得する
            $db = new Users();
            $data = $db->getUser($id);
            if(empty($data)) return redirect('/admin/top');

            Auth::user()->user_name = $data->user_name;
            session()->flash('flashmessage','ユーザー情報を更新しました。');
        }
        return redirect('/admin/user/edit?id=' . $id);
    }

    /**
     * deleteConfirm
     * 削除確認
     * @access public
     * @param Request $request
     */
    public function deleteConfirm(Request $request)
    {
        $id = $request->id;
        $this->checkId($id);
        $this->checkAdmin();
        // ログイン中のユーザーは削除不可
        if($id == Auth::user()->id) return redirect('/admin/top');

        $request->validate(['id' => ['required','integer']]);

        $db = new Users();
        $data = $db->getUser($id);
        if(!$data) return redirect('/admin/top');

        $data->auth = config('umekoset.auth.' . $data->auth);
        return view('admin.user.delete-confirm', compact('data'));
    }

    /**
     * deleteProc
     * 削除処理
     * @access public
     * @param Request $request
     */
    public function deleteProc(Request $request)
    {
        $id = $request->id;
        $this->checkId($id);
        $this->checkAdmin();
        // ログイン中のユーザーは削除不可
        if($id == Auth::user()->id) return redirect('/admin/top');

        $request->validate(['id' => ['required','integer']]);

        $db = new Users();
        $data = $db->getUser($id);
        if(!$data) return redirect('/admin/top');

        $result = $db->deleteUser($id);
        if($result == 1) {
            session()->flash('flashmessage','指定したユーザーを削除しました。');
        } else {
            session()->flash('flashmessage','ユーザーの削除に失敗しました。');
        }
        return redirect('/admin/user');
    }
}
