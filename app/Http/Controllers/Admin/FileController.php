<?php
/**
 * FileController
 * 管理画面 ファイル
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SaveFile;
use App\Models\Article;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class FileController extends Controller
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
     * @access public
     * @param Request $request
     * ファイル一覧画面
     */
    public function index(Request $request)
    {
        $db = new SaveFile();
        $file = $db->getList();

        foreach($file as $key=>$data) {
            $file[$key]->thumbnail = asset('storage/uploads/image/' . $data->year . '/' . $data->month . '/small/' . $data->filename);
        }

        return view('admin.file.index', compact('file'));
    }

    /**
     * deleteConfitm
     * ファイルの削除確認
     * @access public
     * @param Request $request
     */
    public function deleteConfirm(Request $request)
    {
        $this->checkAdmin();
        $id = $request->id;
        $data = '';
        if(empty($id)) return redirect('/admin/file');
        $request->validate(['id' => ['required','integer']]);
        // ファイル情報の取得
        $db = new SaveFile();
        $data = $db->getFileEdit($id);
        if(!isset($data[0])) return redirect('/admin/file');
        $data = $data[0];
        $data->thumbnail = asset('storage/uploads/image/' . $data->year . '/' . $data->month . '/middle/' . $data->filename);

        // このファイルを使用している記事を探す
        $dbArt = new Article();
        $postData = $dbArt->searchRelatedFile($id, $data->filename);
        foreach($postData as $key=>$post) {
            // アイキャッチ画像で使われているか
            $postData[$key]->icatchFlg = false;
            if($post->icatch == $id) {
                $postData[$key]->icatchFlg = true;
            }
            $postData[$key]->postFlg = false;
            if(mb_strpos($post->contents, $data->filename) !== false) {
                $postData[$key]->postFlg = true;
            }

            // 本文のタグを消して表示する
            $str = strip_tags(str_replace('<br>', "\n", str_replace('</p>', "\n", $post->contents)));
            if(mb_strlen($str) > 100) {
                $str = mb_substr($str, 0, 100);
                $str .= ' ...';
            }
            $str = str_replace("\n", '<br>', $str);
            $postData[$key]->contents = $str;
        }

        return view('admin.file.delete-confirm', compact('id', 'data', 'postData'));
    }

    /**
     * deleteProc
     * ファイルの削除処理
     * @access public
     * @param Request $request
     */
    public function deleteProc(Request $request)
    {
        $this->checkAdmin();
        $id = $request->id;
        if(empty($id)) return redirect('/admin/file');
        $request->validate(['id' => ['required','integer']]);
        $db = new SaveFile();
        // ファイルの存在確認
        $data = $db->getFile($id);
        if(!isset($data[0])) return redirect('/admin/file');
        $data = $data[0];

        // 物理ファイルの削除
        $imgSizes = config('umekoset.image_size');
        $delFile = [];
        foreach($imgSizes as $size=>$imgSize) {
            $path = 'public/uploads/image/' . $data->year . '/' . $data->month . '/' .$size . '/' . $data->filename;
            if(Storage::disk('local')->exists($path)) {
                $delFile[] = $path;
            }
        }
        $result = true;
        if($delFile) $result = Storage::disk('local')->delete($delFile);
        if(!$result) {
            session()->flash('flashmessage', 'ファイルの削除に失敗しました。');
            return redirect('/admin/file');
        }

        // ファイルの削除
        $result = $db->deleteFile($id);

        if(!$result) {
            session()->flash('flashmessage', 'ファイル情報の削除に失敗しました。');
            return redirect('/admin/file');
        }

        // ファイル一覧へリダイレクトする
        if($result == 1) {
            session()->flash('flashmessage', 'ファイルを削除しました。');
            return redirect('/admin/file');
        } else {
            session()->flash('flashmessage', 'ファイルの削除に失敗しました。');
            return redirect('/admin/file');
        }
    }

    /**
     * edit
     * ファイル情報編集画面
     * 編集できるのは説明文だけ
     * @access public
     * @param Request $request
     */
    public function edit(Request $request)
    {
        $id = $request->id;
        if(empty($id)) return redirect('/admin/file');

        $data = '';
        $db = new SaveFile();
        $request->validate(['id' => ['required','integer']]);
        // 記事の内容の取得
        $data = $db->getFileEdit($id);
        if(!isset($data[0])) return redirect('/admin/file');
        $data = $data[0];
        $data->thumbnail = asset('storage/uploads/image/' . $data->year . '/' . $data->month . '/middle/' . $data->filename);
        return view('admin.file.edit', compact('id', 'data'));
    }

    /**
     * editProc
     * ファイル情報編集処理
     * @access public
     * @param Request $request
     */
    public function editProc(Request $request)
    {
        $id = $request->id;
        if(empty($id)) return redirect('/admin/file');
        $now = Carbon::now();
        $db = new SaveFile();
        $request->validate(['id' => ['required','integer']]);
        $data = $db->getFileEdit($id);
        if(!isset($data[0])) return redirect('/admin/file');
        // 一般ユーザーはファイル所有者でなければ編集できない
        if(Auth::user()->auth != config('umekoset.auth_admin')){
            if($data[0]->user_id != Auth::user()->id) return redirect('/admin/file');
        }

        // 説明文はHTMLタグ不許可
        $request['description'] = htmlspecialchars($request['description'], ENT_QUOTES, 'UTF-8');

        $checkValid = [
            'description' => ['max:255'],
        ];
        // Validate
        $request->validate($checkValid);

        // ファイル情報の更新
        $updData = [];
        $updData['id'] = $id;
        $updData['description'] = $request['description'];
        $updData['updated_at'] = $now;

        // 更新
        $updData['id'] = $id;
        $result = $db->updateFileData($updData);
        // editへリダイレクトする
        if($result == 1) {
            // 更新されたデータを取得する
            $data = $db->getFileEdit($id);
            if(empty($data)) return redirect('/home');
            session()->flash('flashmessage','情報を更新しました。');
            return redirect('/admin/file/edit?id=' . $id);
        } else {
            session()->flash('flashmessage', '更新失敗しました。');
            session()->flash('description', $request['description']);
            return redirect('/admin/file/edit?id=' . $id);
        }
    }
}
