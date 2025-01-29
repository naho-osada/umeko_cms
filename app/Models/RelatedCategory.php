<?php
/**
 * RelatedCategoryテーブル
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RelatedCategory extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'related_category';
    protected $guarded = ['id'];
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * getCategories
     * 指定記事IDのカテゴリを取得する
     * @param $id article_id
     * @return $categories
     */
    public function getCategories($artId)
    {
        $data = '';
        if(empty($artId)) return $data;
        $table = DB::table($this->table);
        $data = $table
                    ->leftjoin('category as cat', 'cat.id', '=', 'related_category.category_id')
                    ->select('cat.id as category_id', 'related_category.id as rel_id', 'cat.category_name', 'cat.disp_name')
                    ->orderByRaw('sort_no is null asc') // NULLは後ろへ
                    ->orderBy('sort_no', 'asc')
                    ->orderBy('cat.id', 'asc')
                    ->where('related_category.article_id', $artId)
                    ->get()
                    ->toArray();
        return $data;
    }

    /**
     * getRelCats
     * 指定カテゴリIDのrelated_idを取得する
     * @param $catId カテゴリID
     * @return $data
     */
    public function getRelCats($catId)
    {
        $data = '';
        if(empty($catId)) return $data;
        $table = DB::table($this->table);
        $data = $table
                    ->select('id')
                    ->orderBy('id', 'asc')
                    ->where('category_id', $catId)
                    ->get()
                    ->toArray();
        return $data;

    }

    /**
     * getRelCatArticle
     * 指定カテゴリIDの記事を取得する
     * @access public
     * @param $catId
     * @return $data
     */
    public function getRelCatArticle($catId)
    {
        $data = '';
        if(empty($catId)) return $data;
        $table = DB::table($this->table . ' as relcat');
        $data = $table
                    ->join('article as art', 'relcat.article_id', '=', 'art.id')
                    ->leftjoin('save_file as s_file', 's_file.id', '=', 'art.icatch')
                    ->select('art.title', 'art.contents', 'art.status', 'art.path', 'art.icatch', 's_file.year as icatch_y', 's_file.month as icatch_m', 's_file.filename as icatch_file', 'art.updated_at')
                    ->orderBy('art.publish_at', 'desc')
                    ->orderBy('art.id', 'asc')
                    ->where('relcat.category_id', $catId)
                    ->get()
                    ->toArray();
        return $data;
    }

    /**
     * getRelCatNameArticle
     * 指定カテゴリ名の記事を取得する
     * @access public
     * @param $name
     * @return $data
     */
    public function getRelCatNameArticle($name='', $htmlFlag=false)
    {
        $data = '';
        if(empty($name)) return $data;
        $num = !empty(config('umekoset.archive_list_num')) ? config('umekoset.archive_list_num') : config('umekoset.default_index_num');
        $now = Carbon::now();
        $table = DB::table($this->table . ' as relcat');
        $table
            ->join('article as art', 'relcat.article_id', '=', 'art.id')
            ->join('category as cat', 'cat.id', '=', 'relcat.category_id')
            ->leftjoin('save_file as s_file', 's_file.id', '=', 'art.icatch')
            ->select('art.id', 'art.title', 'art.contents', 'art.status', 'art.publish_at', 'art.path', 'art.icatch', 's_file.year as icatch_y', 's_file.month as icatch_m', 's_file.filename as icatch_file', 'art.updated_at')
            ->orderBy('art.publish_at', 'desc')
            ->orderBy('art.id', 'asc')
            ->where('art.status', '=', config('umekoset.status_publish'))
            ->where('cat.category_name', $name)
            ->where('art.publish_at', '<=', $now);
        if(!$htmlFlag) {
            $data = $table->paginate($num);
        } else {
            // HTMLページだったら全件取得
            $data = $table->get();
        }
        return $data;
    }

    /**
     * getRelCatArticles
     * 指定カテゴリID配列の記事を取得する
     * 公開側関連記事の表示で使用
     * @param $catIds カテゴリID
     * @param $articleId 検索から除外するID（表示中の自分のページは関連記事から除外する）
     * @return $data
     */
    public function getRelCatArticles($catIds=[], $articleId='')
    {
        $data = [];
        if(empty($catIds)) return $data;
        if(empty($articleId)) return $data;
        $num = !empty(config('umekoset.related_article_num')) ? config('umekoset.related_article_num') : config('umekoset.default_index_num');
        $now = Carbon::now();

        $table = DB::table($this->table . ' as relcat');
        $data = $table
                    ->join('article as art', 'relcat.article_id', '=', 'art.id')->where('art.id', '!=', $articleId)->where('art.status', config('umekoset.status_publish'))
                    ->leftjoin('save_file as s_file', 's_file.id', '=', 'art.icatch')
                    ->select('art.title', 'art.contents', 'art.status', 'art.path', 'art.icatch', 'art.publish_at', 'art.updated_at', 's_file.year as icatch_y', 's_file.month as icatch_m', 's_file.filename as icatch_file')
                    ->orderBy('art.publish_at', 'desc')
                    ->whereIn('relcat.category_id', $catIds)
                    ->where('art.publish_at', '<=', $now)
                    ->limit($num)
                    ->distinct()
                    ->get('art.id');
        return $data;
    }

    /**
     * addRelCategory
     * カテゴリーの追加
     * @param $data
     * @return $id
     */
    public function addRelCategory($data)
    {
        $id = DB::table($this->table)
            ->insertGetId($data);
        return $id;
    }

    /**
     * deleteRelCategory
     * カテゴリーの削除
     * @param $id related_id
     * @return $result
     */
    public function deleteRelCategory($id)
    {
        if(empty($id)) return false;
        $result = DB::table($this->table)
            ->where('id', $id)
            ->delete();
        return $result;
    }

    /**
     * getRelCategory
     * 指定related_idのデータを取得する
     * 試験用
     * @param $id
     * @return $data
     */
    public function getRelCategory($id)
    {
        $data = '';
        if(empty($id)) return $data;
        $table = DB::table($this->table);
        $data = $table
                    ->where('id', $id)
                    ->get();
        return $data;
    }
}
