<?php
/**
 * Users テーブル
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class Users extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $guarded = ['id'];

    /**
     * getList
     * 一覧用のデータを取得する
     * @access public
     * @return $data
     */
    public function getList()
    {
        $data = DB::table($this->table)
            ->selectRaw('id, user_name, auth')
            ->orderBy('auth', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->toArray();
        return $data;
    }

    /**
     * getUser
     * 一人のユーザー情報を取得する
     * @access public
     * @param $id ユーザーID
     * @return $data
     */
    public function getUser($id)
    {
        $data = DB::table($this->table)
            ->selectRaw('id, user_name, email, auth')
            ->where('id', '=', $id)
            ->get();
        if(isset($data[0])) {
            return $data[0];
        } else {
            return false;
        }
    }

    /**
      * addUser
      * ユーザーを登録する
      * @access public
      * @param $data
      * @return $id 失敗した場合は空
      */
    public function addUser($data)
    {
        $id = DB::table($this->table)
            ->insertGetId($data);
        return $id;
    }

    /**
     * updateUser
     * ユーザー情報を更新する
     * @access public
     * @param $data
     * @return $result
     */
    public function updateUser($data)
    {
        $result = DB::table($this->table)
            ->where('id', $data['id'])
            ->update($data);
        return $result;
    }

    /**
     * deleteUser
     * ユーザー情報を削除する
     * @access public
     * @param $id
     * @return $result
     */
    public function deleteUser($id)
    {
        $result = DB::table($this->table)
            ->where('id', $id)
            ->delete();
        return $result;
    }

    /**
     * getUserTest
     * 更新テスト用
     * 一人のユーザー情報を取得する
     * @access public
     * @param $id ユーザーID
     * @return $data
     */
    public function getUserTest($id)
    {
        $data = DB::table($this->table)
            ->where('id', '=', $id)
            ->get();
        if(isset($data[0])) {
            return $data[0];
        } else {
            return false;
        }
    }

    /**
     * getUserLastID
     * レコードの最後のIDを取得する
     * @access public
     * @return $data[0]->id
     */
    public function getUserLastID()
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
