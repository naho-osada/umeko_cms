<?php
/**
 * Categoryテーブル
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Category extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'category';
    protected $guarded = ['id'];
    protected $dates = [
        'publish_at',
        'created_at',
        'updated_at',
    ];

    /**
     * getList
     * 一覧用のデータを取得する
     * @access public
     * @return $data
     */
    public function getList()
    {
        $table = DB::table($this->table . ' as cat');
        $data = $table
                    ->leftJoin('related_category as relcat', 'relcat.category_id', '=' , 'cat.id')
                    ->select('cat.id', DB::raw('max(category_name) as category_name'), DB::raw('max(disp_name) as disp_name'), DB::raw('max(user_id) as user_id'), DB::raw('count(relcat.article_id) as article_cnt'))
                    ->groupBy('cat.id')
                    ->orderByRaw('sort_no is null asc') // NULLは後ろへ
                    ->orderBy('sort_no', 'asc')
                    ->orderBy('cat.id', 'asc')
                    ->get();
        return $data;
    }

    /**
     * getListPublic
     * 一覧用のデータを取得する
     * @access public
     * @return $data
     */
    public function getListPublish()
    {
        $now = Carbon::now();
        $table = DB::table($this->table . ' as cat');
        $data = $table
                    ->leftJoin('related_category as relcat', 'relcat.category_id', '=' , 'cat.id')
                    ->leftJoin('article as art', 'art.id', '=' , 'relcat.article_id')->where('status', '=', config('umekoset.status_publish'))->where('art.publish_at', '<=', $now)
                    ->select('cat.id', DB::raw('max(category_name) as category_name'), DB::raw('max(disp_name) as disp_name'), DB::raw('max(cat.user_id) as user_id'), DB::raw('count(relcat.article_id) as article_cnt'))
                    ->groupBy('cat.id')
                    ->orderByRaw('sort_no is null asc') // NULLは後ろへ
                    ->orderBy('sort_no', 'asc')
                    ->orderBy('cat.id', 'asc')
                    ->get();
        return $data;
    }

    /**
     * getCategory
     * カテゴリ情報を取得する
     * @access public
     * @param $search id / user_id
     * @return $data
     */
    public function getCategory($search)
    {
        $data = [];
        if(empty($search)) return $data;
        if(empty($search['id'])) return $data;
        $table = DB::table($this->table);
        $table
            ->select('id', 'category_name', 'disp_name', 'sort_no', 'updated_at')
            ->where('id', $search['id']);
        if(isset($search['user_id'])) {
            $table
                ->where('user_id', $search['user_id']);
        }
        $data = $table
            ->get()
            ->toArray();
        return $data;
    }

    /**
     * getCategoryAll
     * カテゴリ情報を全項目取得する
     * 試験用
     * @access public
     * @param $id
     * @return $data
     */
    public function getCategoryAll($id)
    {
        $data = [];
        if(empty($id)) return $data;
        $table = DB::table($this->table);
        $data = $table
            ->where('id', $id)
            ->get()
            ->toArray();
        return $data;
    }

    /**
     * getCategoryName
     * カテゴリ名からカテゴリ情報を取得する
     * @access public
     * @param $name
     * @return $data
     */
    public function getCategoryName($name='')
    {
        $data = [];
        if(empty($name)) return $data;
        $table = DB::table($this->table);
        $table
            ->select('id', 'category_name', 'disp_name')
            ->where('category_name', $name);
        $data = $table
            ->get();
        return $data;
    }

    /**
     * getCategoryNum
     * 現在登録されているカテゴリー数を取得
     * @access public
     * @return count
     */
    public function getCategoryNum()
    {
        $table = DB::table($this->table);
        return $table->count();
    }

    /**
     * addCategory
     * カテゴリーを登録する
     * @access public
     * @param $data
     * @return $id 失敗した場合は空
     */
    public function addCategory($data)
    {
        $id = DB::table($this->table)
            ->insertGetId($data);
        return $id;
    }

    /**
     * updateCategory
     * カテゴリーを更新する
     * @access public
     * @param $data
     * @return $result
     */
    public function updateCategory($data)
    {
        if(empty($data['id'])) return false;
        $result = DB::table($this->table)
            ->where('id', $data['id'])
            ->update($data);
        return $result;
    }

    /**
     * deleteCategory
     * 記事を削除する
     * @access public
     * @param $id
     * @return $result
     */
    public function deleteCategory($id)
    {
        if(empty($id)) return false;
        $result = DB::table($this->table)
            ->where('id', $id)
            ->delete();
        return $result;
    }

    /**
     * getCategoryLastID
     * レコードの最後のIDを取得する
     * @access public
     * @return $data[0]->id
     */
    public function getCategoryLastID()
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
}
