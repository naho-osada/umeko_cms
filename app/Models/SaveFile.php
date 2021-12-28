<?php
/**
 * SaveFileテーブル
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class SaveFile extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'save_file';
    protected $guarded = ['id'];

    /**
     * addFile
     * アップロードしたファイルのデータを登録する
     * @access public
     * @return $data
     */
    public function addFile($data)
    {
        $id = DB::table($this->table)
            ->insertGetId($data);
        return $id;
    }

    /**
     * updateFileData
     * ファイル情報を更新する
     * @access public
     * @param $data
     * @return $result
     */
    public function updateFileData($data)
    {
        if(empty($data['id'])) return false;
        $result = DB::table($this->table)
            ->where('id', $data['id'])
            ->update($data);
        return $result;
    }

    /**
     * deleteFile
     * ファイル情報を削除する
     * @access public
     * @param $id
     * @return $result
     */
    public function deleteFile($id)
    {
        if(empty($id)) return false;
        $result = DB::table($this->table)
            ->where('id', $id)
            ->delete();
        return $result;
    }

    /**
     * getFile
     * 指定IDのファイルを取得する
     * 試験用
     * @access public
     * @param $id
     * @return $data
     */
    public function getFile($id) {
        $table = DB::table($this->table);
        $data = $table
            ->where('id', $id)
            ->get();
        return $data;
    }

    /**
     * getFileLastID
     * レコードの最後のIDを取得する
     * @access public
     * @return $data[0]->id
     */
    public function getFileLastID()
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
     * getList
     * 存在するファイルを取得する
     * @access public
     * @return $data
     */
    public function getList()
    {
        $table = DB::table($this->table);
        $table
            ->leftjoin('users as user', 'user.id', '=', 'save_file.user_id')
            ->select('save_file.id', 'save_file.year', 'save_file.month', 'save_file.filename', 'save_file.description', 'save_file.user_id', 'save_file.created_at', 'user.user_name')
            ->orderBy('save_file.created_at', 'desc')
            ->orderBy('save_file.id', 'asc');
        $num = !empty(config('umekoset.file_index_num')) ? config('umekoset.file_index_num') : config('umekoset.default_index_num');
        $data = $table
            ->paginate($num);
        return $data;
    }

    /**
     * getFileEdit
     * 編集用のファイル情報を取得する
     * @access public
     * @param $id
     * @return $data
     */
    public function getFileEdit($id)
    {
        if(empty($id)) return false;
        $table = DB::table($this->table);
        $data = $table
            ->leftjoin('users as user', 'user.id', '=', 'save_file.user_id')
            ->select('save_file.id', 'save_file.year', 'save_file.month', 'save_file.filename', 'save_file.description', 'save_file.user_id', 'save_file.created_at', 'save_file.updated_at', 'save_file.user_id', 'user.user_name')
            ->where('save_file.id', $id)
            ->get();
        return $data;
    }
}
