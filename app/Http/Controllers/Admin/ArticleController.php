<?php
/**
 * ArticleController
 * 管理画面 投稿記事
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Category;
use App\Models\RelatedCategory;
use App\Rules\ImageEx;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\SaveFile;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use App\Rules\HalfStringSymbol;
use App\Rules\ArticleStatus;
use App\Rules\ArticleAuth;
use App\Rules\CategoryValid;
use App\Library\CommonPublic;

class setDate {
    public function __construct()
    {
        $this->open_year = date('Y');
        $this->open_month = date('m');
        $this->open_day = date('d');
        $this->open_hour = date('H');
        $this->open_min = date('i');
        $this->open_seconds = '00';
        $this->icatch = '';
    }

    public function defaultValue() {
        return $this;
    }
}

class ArticleController extends Controller
{
    public function __construct(Route $route)
    {
        $this->middleware('auth');
        $actions = explode('@', $route->getActionName());
        $this->action = $actions[1];
    }

    /**
     * index
     * 記事一覧画面
     * @access public
     * @param Request $request
     */
    public function index(Request $request)
    {
        $search = $this->setDefaultParams();
        $search['status'] = $request->status;
        if(!empty($search['status'])) {
            $request->validate(['status' => [new ArticleStatus]]);
        }
        $db = new Article();
        $article = $db->getList($search);

        return view('admin.article.index', compact('article', 'search'));
    }

    /**
     * private
     * 記事一覧の非公開処理
     * @access public
     * @param Request $request
     */
    public function private(Request $request)
    {
        $id = $request->id;
        if(empty($id)) return redirect('/admin/article');

        $request->validate(['id' => ['required','integer']]);
        // 該当IDのものが更新可能か確認する
        $search = $this->setDefaultParams($id);
        $db = new Article();
        $data = $db->getArticle($search);
        if(empty($data)) return redirect('/admin/article');

        // 記事のステータス更新
        // 記事の登録、更新
        $updData = [];
        $updData['id'] = $id;
        $updData['status'] = config('umekoset.status_private');
        $updData['updated_user_id'] = Auth::user()->id;
        $updData['updated_at'] = Carbon::now();

        $db = new Article();
        $result = $db->updateArticle($updData);
        // 記事一覧へリダイレクトする
        if($result == 1) {
            // 更新されたデータを取得する
            $search = $this->setDefaultParams($id);
            $data = $db->getArticle($search);
            if(empty($data)) return redirect('/home');
            $data = $data[0];
            session()->flash('flashmessage', '「' . $data->title . '」を' . config('umekoset.status.' . $data->status) . 'にしました。');
            return redirect('/admin/article');
        } else {
            session()->flash('flashmessage', '記事の非公開に失敗しました。');
            return redirect('/admin/article');
        }
    }

    /**
     * publish
     * 記事一覧の公開処理
     * @access public
     * @param Request $request
     */
    public function publish(Request $request)
    {
        $id = $request->id;
        if(empty($id)) return redirect('/admin/article');

        $request->validate(['id' => ['required','integer']]);
        // 該当IDのものが更新可能か確認する
        $search = $this->setDefaultParams($id);
        $db = new Article();
        $data = $db->getArticle($search);
        if(empty($data)) return redirect('/admin/article');

        // 記事のステータス更新
        // 記事の登録、更新
        $updData = [];
        $updData['id'] = $id;
        $updData['status'] = config('umekoset.status_publish');
        $updData['updated_user_id'] = Auth::user()->id;
        $updData['updated_at'] = Carbon::now();

        $db = new Article();
        $result = $db->updateArticle($updData);
        // 記事一覧へリダイレクトする
        if($result == 1) {
            // 更新されたデータを取得する
            $search = $this->setDefaultParams($id);
            $data = $db->getArticle($search);
            if(empty($data)) return redirect('/home');
            $data = $data[0];
            session()->flash('flashmessage', '「' . $data->title . '」を' . config('umekoset.status.' . $data->status) . 'にしました。');
            return redirect('/admin/article');
        } else {
            session()->flash('flashmessage', '記事の公開に失敗しました。');
            return redirect('/admin/article');
        }
    }

    /**
     * deleteConfirm
     * 記事の削除確認
     * @access public
     * @param Request $request
     */
    public function deleteConfirm(Request $request)
    {
        $id = $request->id;
        $data = '';
        if(empty($id)) return redirect('/admin/article');
        $request->validate(['id' => ['required','integer']]);
        // 記事の内容の取得
        $db = new Article();
        $search = $this->setDefaultParams($id);
        $data = $db->getArticle($search);
        if(empty($data)) return redirect('/admin/article');
        $data = $data[0];
        // 本文のタグを消して表示する
        $str = strip_tags(str_replace('<br>', "\n", str_replace('</p>', "\n", $data->contents)));
        if(mb_strlen($str) > 250) {
            $str = mb_substr($str, 0, 250);
            $str .= ' ...';
        }
        $str = str_replace("\n", '<br>', $str);
        $data->contents = $str;
        // アイキャッチ画像の設定
        $data->icatch_thumbnail = '';
        if($data->icatch) {
            $data->icatch_thumbnail = asset('storage/uploads/image/' . $data->icatch_y . '/' . $data->icatch_m . '/small/' . $data->icatch_file);
        }

        return view('admin.article.delete-confirm', compact('id', 'data'));
    }

    /**
     * deleteProc
     * 記事一覧の削除処理
     * @access public
     * @param Request $request
     */
    public function deleteProc(Request $request)
    {
        $id = $request->id;
        if(empty($id)) return redirect('/admin/article');
        $request->validate(['id' => ['required','integer']]);
        $db = new Article();
        // 記事の存在確認
        $search = $this->setDefaultParams($id);
        $data = $db->getArticle($search);
        if(!$data) return redirect('/admin/article');
        // 記事の削除
        $result = $db->deleteArticle($id);
        // 記事一覧へリダイレクトする
        if($result == 1) {
            session()->flash('flashmessage', '記事を削除しました。');
            return redirect('/admin/article');
        } else {
            session()->flash('flashmessage', '記事の削除に失敗しました。');
            return redirect('/admin/article');
        }
    }

    /**
     * edit
     * 記事投稿&編集画面
     * @access public
     * @param Request $request
     */
    public function edit(Request $request)
    {
        $id = $request->id;
        $data = '';
        $relCategories = [];
        if(!empty($id)) {
            $request->validate(['id' => ['required','integer']]);
            $search = $this->setDefaultParams($id);
            // 記事の内容の取得
            $db = new Article();
            $data = $db->getArticle($search);
            if(empty($data)) return redirect('/article');
            $data = $data[0];
            // 公開日時の設定
            $data->open_year = date('Y', strtotime($data->publish_at));
            $data->open_month = date('m', strtotime($data->publish_at));
            $data->open_day = date('d', strtotime($data->publish_at));
            $data->open_hour = date('H', strtotime($data->publish_at));
            $data->open_min = date('i', strtotime($data->publish_at));
            $data->open_seconds = date('s', strtotime($data->publish_at));
            // アイキャッチ画像の設定
            $data->icatch_thumbnail = '';
            if($data->icatch) {
                $data->icatch_thumbnail = asset('storage/uploads/image/' . $data->icatch_y . '/' . $data->icatch_m . '/small/' . $data->icatch_file);
            }
            // カテゴリ情報の取得
            $relCatDb = new RelatedCategory();
            $relData = $relCatDb->getCategories($id);
            if(!empty($relData)) {
                foreach($relData as $reld) {
                    $relCategories[] = $reld->category_id;
                }
            }
        } else {
            // 新規登録の場合は現在日時を公開日時にセットする
            $setDate = new setDate();
            $data = $setDate->defaultValue();
            $data->icatch_thumbnail = '';
        }
        // 現在年の設定
        $nowYear = date('Y');
        // 登録されているカテゴリを取得
        $catDb = new Category();
        $categories = $catDb->getList();

        return view('admin.article.edit', compact('id', 'data', 'nowYear', 'categories', 'relCategories'));
    }

    /**
     * editProc
     * 記事投稿&編集処理
     * @access public
     * @param Request $request
     */
    public function editProc(Request $request)
    {
        $id = $request->id;
        $now = Carbon::now();
        $db = new Article();
        if(!empty($id)) {
            $request->validate(['id' => ['required','integer']]);
            // 該当IDのものが更新可能か確認する
            $search = $this->setDefaultParams($id);
            $db = new Article();
            $data = $db->getArticle($search);
            if(empty($data)) return redirect('/admin/article');
        }

        // 投稿タイトルはHTMLタグ不許可
        $request['post_title'] = htmlspecialchars($request['post_title'], ENT_QUOTES, 'UTF-8');
        // HTML Purifier設定があるときは本文の不要タグ（scriptなど）を除去する
        if(config('umekoset.html_purifier')) $request['trumbowyg-editor'] = clean($request['trumbowyg-editor']);

        // SEO descriptionはHTMLタグと改行を除去する
        $request['seo_description'] = strip_tags($request['seo_description']);
        $request['seo_description'] = str_replace("\r\n", '', $request['seo_description']);
        $request['seo_description'] = str_replace("\n", '', $request['seo_description']);
        // 編集権限のvalidateを追加する
        $checkValid = [
            'post_title' => ['required', 'max:255'],
            'trumbowyg-editor' => ['required', 'max:4294967000'], // longtext型
            'status' => [new ArticleStatus],
            'path' => ['max:255', new HalfStringSymbol, 'unique:article,path,' . $id . ',id'],
            'article_auth' => [new ArticleAuth],
            'seo_description' => ['max:255'],
        ];
        // 公開日時を生成する
        $publishDate = '';
        // 公開日時の値があればセットする（デフォルト値がセットされるので、本来空で来ることはない）
        if(isset($request['open_year']) && isset($request['open_month']) && isset($request['open_day']) && isset($request['open_hour']) && isset($request['open_min'])) {
            $publishDate = $request['open_year'] . '-' . $request['open_month'] . '-' .$request['open_day'] . ' ' . $request['open_hour'] . ':' . $request['open_min'] . ':00';
        }
        $request['publish_at'] = $publishDate;
        // 公開日時が空のときは現在日時を入れる
        if($request['publish_at'] == '') {
            $request['publish_at'] = $now;
        } else {
            // 値が入っているときはチェックを通す
            $checkValid['publish_at'] = ['date_format:Y-m-d H:i:s'];
        }
        // 保存先パスが空の時は日時とする
        if($request['path'] == '') {
            $request['path'] = date('dHis');
        }
        // カテゴリがある場合はそのカテゴリの存在チェックを行う
        if($request['category']) {
            $checkValid['category'] = [new CategoryValid];

        }

        // アイキャッチ画像
        // 選択されている場合のみ（更新で前の画像と同じときは更新対象にならない）
        if($request->file('icatch')) {
            $file = $request->file('icatch')->getClientOriginalName();
            $mineType = $request->file('icatch')->getMimeType();
            $request['filetype'] = $mineType;
            $checkValid['icatch'] = ['file', 'max:' . config('umekoset.max_filesize')];
            $checkValid['filetype'] = [new ImageEx];
        }

        // Validate
        $request->validate($checkValid);

        // アイキャッチ画像のアップロード
        $icatch = '';
        if($request->file('icatch')) {
            $alt = $request['title'] . 'の画像';
            $icatch = $this->upload($file, 'icatch', $alt);
            if(!$icatch) {
                session()->flash('flashmessage','アイキャッチ画像の保存に失敗しました。');
                session()->flash('publish_at', $request['publish_at']);
                session()->flash('status', $request['status']);
                session()->flash('path', $request['path']);
                session()->flash('article_auth', $request['article_auth']);
                session()->flash('path', $request['path']);
                session()->flash('trumbowyg-editor', $request['trumbowyg-editor']);
                session()->flash('seo_description', $request['seo_description']);
                return redirect('/admin/article/edit');
            }
        }

        // 記事の登録、更新
        $updData = [];
        $updData['title'] = $request['post_title'];
        $updData['contents'] = $request['trumbowyg-editor'];
        $updData['status'] = $request['status'];
        $updData['path'] = $request['path'];
        $updData['article_auth'] = $request['article_auth'];
        if($request['seo_description'] == '') {
            $updData['seo_description'] = null;
        } else {
            $updData['seo_description'] = $request['seo_description'];
        }
        $updData['publish_at'] = $request['publish_at'];
        $updData['updated_user_id'] = Auth::user()->id;
        $updData['updated_at'] = $now;
        if($request->file('icatch')) {
            $updData['icatch'] = $icatch;
        } else if($request->save_icatch && $request->save_delete) {
            // 更新前にアイキャッチ画像があったが、ファイル更新で送られてこなかった場合は削除
            $updData['icatch'] = null;
        }

        $relCatDb = new RelatedCategory();
        if(empty($id)) {
            // 登録
            $updData['user_id'] = Auth::user()->id;
            $updData['created_at'] = $now;
            $id = $db->addArticle($updData);

            // カテゴリを登録する
            if(isset($request['category'])){
                foreach($request['category'] as $cat) {
                    $updCat = [];
                    $updCat['article_id'] = $id;
                    $updCat['category_id'] = $cat;
                    $relCatDb->addRelCategory($updCat);
                }
            }

            if(!is_int($id)) {
                session()->flash('flashmessage', '投稿失敗しました。');
                session()->flash('publish_at', $request['publish_at']);
                session()->flash('status', $request['status']);
                session()->flash('path', $request['path']);
                session()->flash('post_title', $request['post_title']);
                session()->flash('trumbowyg-editor', $request['trumbowyg-editor']);
                session()->flash('seo_description', $request['seo_description']);
                return redirect('/admin/article/edit');
            } else {
                // 更新されたデータを取得する
                $search = $this->setDefaultParams($id);
                $data = $db->getArticle($search);
                if(empty($data)) {
                    session()->flash('flashmessage', '投稿失敗しました。');
                    session()->flash('publish_at', $request['publish_at']);
                    session()->flash('status', $request['status']);
                    session()->flash('path', $request['path']);
                    session()->flash('article_auth', $request['article_auth']);
                    session()->flash('post_title', $request['post_title']);
                    session()->flash('trumbowyg-editor', $request['trumbowyg-editor']);
                    session()->flash('seo_description', $request['seo_description']);
                    return redirect('/admin/article/edit');
                }
                session()->flash('flashmessage','記事を投稿しました。');
                return redirect('/admin/article/edit?id=' . $id);
            }
        } else {
            // 更新
            $updData['id'] = $id;
            $result = $db->updateArticle($updData);

            // 現在登録されているカテゴリを取得（旧カテゴリ）
            $olds = $relCatDb->getCategories($id);
            $oldCategory = [];
            $oldCategoryId = [];
            if(!empty($olds)) {
                foreach($olds as $old) {
                    $oldCategory[$old->category_id] = $old->category_id;
                    $oldCategoryId[$old->category_id] = $old->rel_id;
                }
            }
            // 新カテゴリをベースに登録判定
            if(isset($request['category'])) {
                foreach($request['category'] as $cat) {
                    // 既に登録されているものは何もしない
                    if(in_array($cat, $oldCategory) !== false) {
                        unset($oldCategory[$cat]);
                        unset($oldCategoryId[$cat]);
                        continue;
                    }
                    // 登録されていないものは登録する
                    $updCat = [];
                    $updCat['article_id'] = $id;
                    $updCat['category_id'] = $cat;
                    $relCatDb->addRelCategory($updCat);
                }
            }
            // 旧カテゴリで余りがあったら削除処理を行う
            if(count($oldCategoryId) > 0) {
                foreach($oldCategoryId as $oldId) {
                    $relCatDb->deleteRelCategory($oldId);
                }
            }

            // editへリダイレクトする
            if($result == 1) {
                // 更新されたデータを取得する
                $search = $this->setDefaultParams($id);
                $data = $db->getArticle($search);
                if(empty($data)) return redirect('/home');
                session()->flash('flashmessage','記事情報を更新しました。');
                return redirect('/admin/article/edit?id=' . $id);
            } else {
                session()->flash('flashmessage', '更新失敗しました。');
                session()->flash('publish_at', $request['publish_at']);
                session()->flash('status', $request['status']);
                session()->flash('path', $request['path']);
                session()->flash('article_auth', $request['article_auth']);
                session()->flash('post_title', $request['post_title']);
                session()->flash('trumbowyg-editor', $request['trumbowyg-editor']);
                session()->flash('seo_description', $request['seo_description']);
                return redirect('/admin/article/edit?id=' . $id);
            }
        }
    }

    /**
     * preview
     * プレビュー機能
     * 現在の投稿画面の状態を表示する。保存はしない
     * Viewは公開画面のものを使用する
     * @access public
     * @param $request
     */
    public function preview(Request $request)
    {
        // 投稿タイトルはHTMLタグ不許可
        $request['post_title'] = htmlspecialchars($request['post_title'], ENT_QUOTES, 'UTF-8');
        // HTML Purifier設定があるときは本文の不要タグ（scriptなど）を除去する
        if(config('umekoset.html_purifier')) $request['trumbowyg-editor'] = clean($request['trumbowyg-editor']);

        // SEO descriptionはHTMLタグと改行を除去する
        $request['seo_description'] = strip_tags($request['seo_description']);
        $request['seo_description'] = str_replace("\r\n", '', $request['seo_description']);
        $request['seo_description'] = str_replace("\n", '', $request['seo_description']);

        $common = new CommonPublic();
        $article = (object)[];
        $article->title = $request['post_title'];
        $article->contents = $common->setTableOfContents($request['trumbowyg-editor']);
        $article->url = ''; // プレビューなのでURLなし
        $article->publish_at = $request->open_year . '-' . $request->open_month . '-' . $request->open_day . ' ' . $request->open_hour . ':' . $request->open_min;
        $article->updated_at = date('Y-m-d H:i:s');
        $article->user_name = Auth::user()->user_name;
        $article->icatch_thumbnail = '';
        // 一時ファイルのときと、既にアップロードされている場合で分ける
        if($request->file('icatch')) {
            // 一時ファイル
            $article->icatch_thumbnail = 'data:' . mime_content_type($request->file('icatch')->getPathName()) . ';base64,' . base64_encode(file_get_contents($request->file('icatch')->getPathName()));
        } else if($request->save_icatch) {
            // アップロード済み
            $saveFile = new SaveFile();
            $icatchData = $saveFile->getFile($request->save_icatch);
            if($icatchData) {
                $article->icatch_thumbnail = asset('storage/uploads/image/' . $icatchData[0]->year . '/' . $icatchData[0]->month . '/large/' . $icatchData[0]->filename);
            }
        }

        // カテゴリ設定
        $relCategories = [];
        if(!empty($request->category)) {
            $CatDb = new Category();
            $catData = $CatDb->getCategories($request->category);
            $relCategories = [];
            foreach($catData as $reld) {
                $relCategories[$reld->id]['url'] = asset('/category/' . $reld->category_name);
                $relCategories[$reld->id]['name'] = $reld->disp_name;
            }
        }

        // Pagerと関連記事は生成しないのでダミーを入れる あくまで「どんな感じで見えるか」を試すだけ
        $pager = 'preview';
        $relArticles = [];
        return view('public.article', compact('article', 'relCategories', 'pager', 'relArticles'));
    }

    /**
     * uploadImage
     * 画像アップロード処理
     * 画面上でエラーメッセージは表示できないので（trumbowyg側の要カスタマイズ、error functionでやると次のアップロードができなくなる問題がある）
     * ステータスコード返却でエラー判定している
     * ステータスコードはコンソールに出るため、エラーの簡易判定は可能
     * 422→アップロード時のValidationエラー
     * 503→storage保存の失敗
     * @access public
     * @param Request $request
     * @return json
     */
    public function uploadImage(Request $request)
    {
        $fileData = $file = $request->file('fileToUpload');
        if(!$fileData) return false;

        $file = $request->file('fileToUpload')->getClientOriginalName();
        $mineType = $request->file('fileToUpload')->getMimeType();
        $alt = $request->alt;
        $request['filename'] = $file;
        $fAry = explode('.', $file);
        $request['file_title'] = array_shift($fAry);
        $request['type'] = $mineType;

        $validator = Validator::make($request->all(), [
            'fileToUpload' => ['required', 'file', 'max:' . config('umekoset.max_filesize')],
            'file_title' => ['required', 'max:200'],
            'type' => [new ImageEx],
            'alt' => ['max:255'],
        ]);
        // エラーがあったらここで返却
        if($validator->fails()) {
            $msgs = $validator->errors()->all();
            $ary = [];
            foreach($msgs as $data) {
                $ary[] = $data;
            }
            $msg = implode(" / ", $ary);
            Log::error($msg);
            return response()->json(['err' => $msg], 422);
        }
        return $this->upload($file, 'fileToUpload', $alt, true);
    }

    /**
     * upload
     * 画像をアップロードする処理
     * @access private
     * @param $file ファイル名
     * @param $key fileのキー
     * @param $alt 画像説明文
     * @param $json json返却するかどうか
     * @return id or json
     */
    private function upload($file, $key, $alt='', $json=false) {
        // storage内に保存する
        $basePath = 'public/uploads/image/';
        $year = date('Y');
        $month = date('m');
        $datePath = $year . '/' . $month . '/';
        // ファイル名に日本語を含む場合はファイル名を強制変更する
        $ary = explode('.', $file);
        $ex = array_pop($ary);
        if(!preg_match('/^[a-zA-Z0-9\-\_]+$/', $ary[0])) {
            $file = date('Ymd') . '.' . $ex;
        }
        // ファイルアップロード
        $imgSizes = config('umekoset.image_size');
        foreach($imgSizes as $size=>$imgSize) {
            // 同名ファイルを検索して存在する場合はファイル名を変更する
            $check = Storage::disk('local')->exists($basePath . $datePath . $size . '/' .  $file);
            if($check) {
                $ary = explode('.', $file);
                $ex = array_pop($ary);
                $file = array_pop($ary) . '_' . date('His') . '.' . $ex;
            }
            // 元画像を一時ファイルへアップロード
            $upFile = request()->file($key)->storeAs($basePath . $datePath . $size . '/', $file);
            // リサイズして上書き保存する
            $img = Image::make(request()->file($key));
            $width = $img->width();
            $height = $img->height();
            if($width >= $height) {
                // 横画像
                $upFile = $img
                            ->resize($imgSize['width'], null, function ($constraint) {$constraint->aspectRatio();})
                            ->save(storage_path('app/' . $basePath . $datePath . $size . '/' . $file));
            } else {
                // 縦画像
                $upFile = $img
                            ->resize(null, $imgSize['height'], function ($constraint) {$constraint->aspectRatio();})
                            ->save(storage_path('app/' . $basePath . $datePath . $size . '/' . $file));
            }
        }
        if($upFile) {
            // ファイル情報をDBに保存する
            $now = Carbon::now();
            $addData = [];
            $addData['year'] = $year;
            $addData['month'] = $month;
            $addData['type'] = config('umekoset.image_type');
            $addData['filename'] = $file;
            $addData['description'] = $alt;
            $addData['user_id'] = Auth::user()->id;
            $addData['created_at'] = $now;
            $addData['updated_at'] = $now;
            $db = new SaveFile();
            $id = $db->addFile($addData);
            // データベースの登録に失敗したらエラー扱いとする
            if(!is_int($id)) {
                // ログに残しておく
                $msg = 'ファイル情報のデータベース保存に失敗しました。';
                Log::error($msg);
                if($json) {
                    return response()->json(['err' => $msg], 503);
                } else {
                    return false;
                }
            }
            $msg = 'ファイルを保存しました。ファイル名：' . $datePath . $file . ' alt：' . $alt;
            Log::info($msg);
            if($json) {
                return response()->json(['success' => true, 'file' => asset('storage/uploads/image/' . $datePath . 'middle/' . $file), 'alt' => $alt]);
            } else {
                // アイキャッチ画像保存ID
                return $id;
            }
        } else {
            $msg = 'ファイルの保存ができません。';
            Log::error($msg);
            if($json) {
                return response()->json(['err' => $msg], 503);
            } else {
                return false;
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
        // 管理者ログイン以外は編集権限が「管理者+作成者」のもののみとする
        if(Auth::user()->auth != 1) {
            $search['article_auth'] = config('umekoset.article_auth_creator');
            $search['user_id'] = Auth::user()->id;
        }
        return $search;
    }
}
