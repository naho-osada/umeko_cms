<?php
/**
 * /app/Models/Users.phpのテスト
 */
namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Users;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase; // テスト用データを自動で元に戻す

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;
    /**
     * 配列を取得していること
     */
    public function test_UsersGetArray()
    {
        $db = new Users();
        $users = $db->getList();
        $this->assertTrue(is_array($users));
    }
    /**
     * 必要な項目を取得していること
     */
    public function test_UsersGetCols()
    {
        $db = new Users();
        $users = $db->getList();

        $cols = ['id', 'user_name', 'auth'];
        $this->assertSame($cols, array_keys((array)$users[0]));
    }

    /**
     * ユーザー編集
     * 必要な項目を取得していること
     */
    public function test_UsersEdit()
    {
        $db = new Users();
        $user = $db->getUser(1);
        $this->assertTrue(is_array((array)$user));
    }

    /**
     * ユーザー編集
     * 必要な項目を取得していること
     */
    public function test_UsersEditCols()
    {
        $db = new Users();
        $user = $db->getUser(1);
        $cols = ['id', 'user_name', 'email', 'auth'];
        $this->assertSame($cols, array_keys((array)$user));
    }

    /**
     * ユーザー編集
     * 必要な情報を1件だけ取得していること
     */
    public function test_UsersEditData()
    {
        $db = new Users();
        $user = $db->getUser(1);
        $this->assertSame(1, $user->id);
    }

    /**
     * ユーザー編集
     * 任意のユーザーの編集が完了できること
     */
    public function test_UsersEditUpdate()
    {
        $now = Carbon::now();
        $db = new Users();
        $updData = [];
        $updData['id'] = 7;
        $updData['user_name'] = 'テスト管理者';
        $updData['email'] = 'clover.kuroi+test@gmail.com';
        $updData['password'] = Hash::make('test02');
        $updData['updated_at'] = $now;
        $result = $db->updateUser($updData);
        // 更新成功していることを確認
        $this->assertSame(1, $result);

        $db = new Users();
        $user = $db->getUserTest(7);
        // データベースから取得したデータと更新したデータがすべて一致することを確認
        $this->assertSame($updData['id'], $user->id);
        $this->assertSame($updData['user_name'], $user->user_name);
        $this->assertSame($updData['email'], $user->email);
        $this->assertSame($updData['password'], $user->password);
        $this->assertSame($now->getTimeStamp(), strtotime($user->updated_at));
    }

    /**
     * ユーザーの追加
     * ユーザーが登録できること
     */
    public function test_UsersAdd() {
        $now = Carbon::now();
        $db = new Users();
        $addData = [];
        $addData['user_name'] = '追加したデータ';
        $addData['email'] = 'clover.kuroi+test02@gmail.com';
        $addData['auth'] = 1;
        $addData['password'] = Hash::make('test02');
        $addData['updated_at'] = $now;
        $id = $db->addUser($addData);
        $this->assertNotNull($id);
        if($id != null) {
            $db = new Users();
            $user = $db->getUserTest($id);
            // データベースから取得したデータと更新したデータがすべて一致することを確認
            $this->assertSame($id, $user->id);
            $this->assertSame($addData['user_name'], $user->user_name);
            $this->assertSame($addData['email'], $user->email);
            $this->assertSame($addData['password'], $user->password);
            $this->assertSame($now->getTimeStamp(), strtotime($user->updated_at));
        }
    }

    /**
     * ユーザーの削除
     * ユーザーが削除できること
     */
    public function test_UsersDelete() {
        // 削除するためのユーザーを追加して、そのデータを削除する
        $now = Carbon::now();
        $db = new Users();
        $addData = [];
        $addData['user_name'] = '削除するためのデータ';
        $addData['email'] = 'clover.kuroi+delete@gmail.com';
        $addData['auth'] = 1;
        $addData['password'] = Hash::make('test');
        $addData['updated_at'] = $now;
        $id = $db->addUser($addData);
        $this->assertNotNull($id);
        if($id != null) {
            $db->deleteUser($id);
            $result = $db->getUserTest($id);
            $this->assertFalse($result);
        }
    }
}
