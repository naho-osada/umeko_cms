<?php
/**
 * Articleテーブル
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Article extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'article';
    protected $guarded = ['id'];
    protected $dates = [
        'publish_at',
        'created_at',
        'updated_at',
    ];

    /**
     * getRecentList
     * 最近更新されたデータを取得する
     * @access public
     * @param $status ステータスのコード
     * @return $data
     */
    public function getRecentList($search=[])
    {
        $recent = Carbon::today()->subDay(7);
        $table = DB::table($this->table);
        $table
            ->leftjoin('users as user', 'user.id', '=', 'article.user_id')
            ->leftjoin('users as user2', 'user2.id', '=', 'article.updated_user_id')
            ->select('article.id', 'article.title', 'article.contents', 'article.user_id', 'article.status', 'article.path', 'article.user_id', 'article.updated_user_id', 'article.publish_at', 'article.updated_at', 'user.user_name', 'user2.user_name as updated_user')
            ->whereDate('article.updated_at', '>=', $recent)
            ->orderBy('article.updated_at', 'desc')
            ->orderBy('article.id', 'asc');
        if(isset($search['article_auth']) && isset($search['user_id'])) {
            $table
                ->where('article_auth', $search['article_auth'])
                ->where('user_id', $search['user_id']);
        }
        $num = !empty(config('umekoset.article_index_num')) ? config('umekoset.article_index_num') : config('umekoset.default_index_num');
        $data = $table->get($num);
        return $data;
    }

    /**
     * getList
     * 一覧用のデータを取得する
     * @access public
     * @param $status ステータスのコード
     * @return $data
     */
    public function getList($search)
    {
        $table = DB::table($this->table);
        $table
            ->leftjoin('users as user', 'user.id', '=', 'article.user_id')
            ->leftjoin('users as user2', 'user2.id', '=', 'article.updated_user_id')
            ->select('article.id', 'article.title', 'article.contents', 'article.user_id', 'article.status', 'article.path', 'article.user_id', 'article.updated_user_id', 'article.publish_at', 'article.updated_at', 'user.user_name', 'user2.user_name as updated_user')
            ->orderBy('article.publish_at', 'desc')
            ->orderBy('article.id', 'asc');
        if(!empty($search['status'])) {
            $table->where('status', $search['status']);
        }
        if(isset($search['article_auth']) && isset($search['user_id'])) {
            $table
                ->where('article_auth', $search['article_auth'])
                ->where('user_id', $search['user_id']);
        }
        $num = !empty(config('umekoset.article_index_num')) ? config('umekoset.article_index_num') : config('umekoset.default_index_num');
        $data = $table
            ->paginate($num);
        return $data;
    }

    /**
     * getArticle
     * 記事情報を取得する
     * @access public
     * @param $search id / article_auth / user_id
     * @return $data
     */
    public function getArticle($search)
    {
        $data = [];
        if(empty($search)) return $data;
        if(empty($search['id'])) return $data;
        $table = DB::table($this->table);
        $table
            ->leftjoin('users', 'users.id', '=', 'article.user_id')
            ->leftjoin('users as user2', 'user2.id', '=', 'article.updated_user_id')
            ->leftjoin('save_file as s_file', 's_file.id', '=', 'article.icatch')
            ->select('article.*', 'users.user_name', 'user2.user_name as updated_user', 's_file.year as icatch_y', 's_file.month as icatch_m', 's_file.filename as icatch_file')
            ->orderBy('article.id', 'asc')
            ->where('article.id', $search['id']);
        if(isset($search['article_auth']) && isset($search['user_id'])) {
            $table
                ->where('article_auth', $search['article_auth'])
                ->where('article.user_id', $search['user_id']);
        }
        $data = $table
            ->get()
            ->toArray();
        return $data;
    }

    /**
     * addArticle
     * 記事を登録する
     * @return $id 失敗した場合は空
     */
    public function addArticle($data)
    {
        $id = DB::table($this->table)
            ->insertGetId($data);
        return $id;
    }

    /**
     * updateArticle
     * 記事を更新する
     */
    public function updateArticle($data)
    {
        if(empty($data['id'])) return false;
        $result = DB::table($this->table)
            ->where('id', $data['id'])
            ->update($data);
        return $result;
    }

    /**
     * deleteArticle
     * 記事を削除する
     */
    public function deleteArticle($id)
    {
        if(empty($id)) return false;
        $result = DB::table($this->table)
            ->where('id', $id)
            ->delete();
        return $result;
    }

    /**
     * getArticleLastID
     * レコードの最後のIDを取得する
     */
    public function getArticleLastID()
    {
        $data = DB::table($this->table)
            ->orderBy('id', 'desc')
            ->limit(1)
            ->get();
        if(isset($data[0])) {
            return $data[0]->id;
        } else {
            return false;
        }
    }

    /**
     * searchRelatedFile
     * 記事に指定のファイル名が含まれるかを探す
     */
    public function searchRelatedFile($fileId='', $filename='')
    {
        $data = [];
        if(empty($fileId) && empty($filename)) return $data;
        $table = DB::table($this->table)->select('article.id', 'article.title', 'article.contents', 'article.status', 'article.path', 'article.icatch', 'article.publish_at', 'updated_at');
        if(!empty($fileId)) {
            $table->orWhere('icatch', '=', $fileId);
        }
        if(!empty($filename)) {
            $table->orWhere('contents', 'LIKE', '%' . $filename . '%');
        }
        $data = $table->get();
        return $data;
    }

    /**
     * getPublishList
     * 公開用の記事データを取得する
     * @access public
     * @return $data
     */
    public function getPublishList()
    {
        $cols = [
            'article.id',
            'article.title',
            'article.contents',
            'article.path',
            'article.icatch',
            'article.publish_at',
            'user.user_name',
            's_file.year as icatch_y',
            's_file.month as icatch_m',
            's_file.filename as icatch_file',
            's_file.description'
        ];
        $num = !empty(config('umekoset.article_index_num')) ? config('umekoset.article_index_num') : config('umekoset.default_index_num');
        $now = Carbon::now();
        $table = DB::table($this->table);
        $data = $table
            ->leftjoin('users as user', 'user.id', '=', 'article.user_id')
            ->leftjoin('save_file as s_file', 's_file.id', '=', 'article.icatch')
            ->select($cols)
            ->where('status', config('umekoset.status_publish'))
            ->where('article.publish_at', '<=', $now)
            ->orderBy('article.publish_at', 'desc')
            ->orderBy('article.id', 'asc')
            ->limit($num)
            ->get();
        return $data;
    }

    /**
     * searchArticle
     * 記事情報の検索（公開画面用）
     * 表示する記事、最近更新された記事で使用
     * @access public
     * @param $search
     * @return $data
     */
    public function searchArticle($search=[])
    {
        $data = [];
        if(empty($search)) return $data;
        $limit = 1;
        $order = 'article.id';
        $sort = 'asc';
        $table = DB::table($this->table);
        $now = Carbon::now();
        $table
            ->leftjoin('users', 'users.id', '=', 'article.user_id')
            ->leftjoin('users as user2', 'user2.id', '=', 'article.updated_user_id')
            ->leftjoin('save_file as s_file', 's_file.id', '=', 'article.icatch')
            ->select('article.*', 'users.user_name', 'user2.user_name as updated_user', 's_file.year as icatch_y', 's_file.month as icatch_m', 's_file.filename as icatch_file')
            ->where('status', config('umekoset.status_publish'))
            ->where('article.publish_at', '<=', $now);

        // 検索条件を追加
        if(isset($search['start_date']) && !empty($search['start_date'])) {
            $table->where('article.publish_at', '>=', $search['start_date']);
        }
        if(isset($search['end_date']) && !empty($search['end_date'])) {
            $table->where('article.publish_at', '<', $search['end_date']);
        }
        if(isset($search['path']) && !empty($search['path'])) {
            $table->where('path', $search['path']);
        }
        if(isset($search['recentday'])) {
            $recent = Carbon::today()->subDay($search['recentday']);
            $table->whereDate('article.updated_at', '>=', $recent);
            $limit = !empty(config('umekoset.sidebar_num')) ? config('umekoset.sidebar_num') : config('umekoset.default_index_num');
            $order = 'article.updated_at';
            $sort = 'desc';
        }
        $data = $table
            ->limit($limit)
            ->orderBy($order, $sort)
            ->get()
            ->toArray();

        return $data;
    }

    /**
     * searchArticlePager
     * 次ページ、前ページのPager用記事情報の検索
     * @access public
     * @return $data
     */
    public function searchArticlePager()
    {
        $data = [];
        $now = Carbon::now();
        $table = DB::table($this->table);
        $data =
            $table
                ->leftjoin('users', 'users.id', '=', 'article.user_id')
                ->leftjoin('users as user2', 'user2.id', '=', 'article.updated_user_id')
                ->leftjoin('save_file as s_file', 's_file.id', '=', 'article.icatch')
                ->select('article.*', 'users.user_name', 'user2.user_name as updated_user', 's_file.year as icatch_y', 's_file.month as icatch_m', 's_file.filename as icatch_file')
                ->where('status', config('umekoset.status_publish'))
                ->where('article.publish_at', '<=', $now)
                ->orderBy('article.id', 'asc')
                ->get();

        return $data;
    }

    /**
     * searchArticleDate
     * 記事情報の日付アーカイブ検索（公開画面用）
     * @access public
     * @param $search
     * @return $data
     */
    public function searchArticleDate($search=[])
    {
        $data = [];
        if(empty($search)) return $data;
        $order = 'article.id';
        $sort = 'asc';
        $table = DB::table($this->table);
        $table
            ->leftjoin('save_file as s_file', 's_file.id', '=', 'article.icatch')
            ->select('article.*', 's_file.year as icatch_y', 's_file.month as icatch_m', 's_file.filename as icatch_file')
            ->where('status', '=', config('umekoset.status_publish'))
            ->where('article.publish_at', '>=', $search['start_date'])
            ->where('article.publish_at', '<', $search['end_date'])
            ->orderBy($order, $sort);

        if(isset($search['all'])) {
            $data = $table->get();
        } else {
            $num = !empty(config('umekoset.archive_list_num')) ? config('umekoset.archive_list_num') : config('umekoset.default_index_num');
            $data = $table->paginate($num);
        }

        return $data;
    }

    /**
     * getRecentUpdArticle
     * TOPページのOGP modified用
     * 最新記事の更新日時を取得する
     * @access public
     * @return $data
     */
    public function getRecentUpdArticle()
    {
        $data = [];
        $table = DB::table($this->table);
        $data =
            $table
                ->select('article.updated_at')
                ->where('status', config('umekoset.status_publish'))
                ->orderBy('updated_at', 'desc')
                ->orderBy('id', 'desc')
                ->limit(1)
                ->get()
                ->toArray();

        return $data[0];
    }

    /**
     * getAllData
     * サイトマップ用全データ取得
     */
    public function getAllData($offset=0, $limit=1000)
    {
        $data = [];
        $table = DB::table($this->table);
        $data =
            $table
                ->select('path', 'publish_at', 'updated_at')
                ->where('status', config('umekoset.status_publish'))
                ->orderBy('updated_at', 'desc')
                ->orderBy('id', 'desc')
                ->offset($offset)
                ->limit($limit)
                ->get()
                ->toArray();

        return $data;
    }
}
