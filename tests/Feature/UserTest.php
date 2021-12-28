<?php
/**
 * ユーザー登録機能の試験
 */
namespace Tests\Feature;

use App\Models\Users;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    // Validateチェック用メールアドレス
    public $mail = 'naho.osada03';
    public $domain = '@gmail.com';

    /**
     * ユーザー関係のページに認証が必要なことを確認する
     */
    public function test_denyAccess()
    {
        $response = $this->get('/admin/user');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');

        $response = $this->get('/admin/user/add');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');

        $response = $this->post('/admin/user/add-proc');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');

        $response = $this->get('/admin/user/edit?id=1');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');

        $response = $this->post('/admin/user/edit-proc?id=1');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');

        $response = $this->get('/admin/user/delete-confirm?id=1');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');

        $response = $this->post('/admin/user/delete-proc?id=1');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');
    }

    /**
     * 管理者ログイン
     * すべてのページが正常に閲覧できることを確認
     */
    public function test_adminAccess()
    {
        // 本番用としてチェックする（CSRFを有効にする）
        $this->app['env'] = 'production';
        $this->dummyAdminLogin();

        $response = $this->get('/admin/user');
        $response
            ->assertStatus(200)
            ->assertSee('管理者')
            ->assertSee('一般ユーザー')
            ->assertSee('一般ユーザー02')
            ->assertSee('一般ユーザー03')
            ->assertSee('一般ユーザー04')
            ->assertSee('一般ユーザー05')
            ->assertSee('管理者02');

        $response = $this->get('/admin/user/add');
        $response->assertStatus(200);

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/user/add-proc');
        $response->assertStatus(419);

        $response = $this->get('/admin/user/edit?id=1');
        $response->assertStatus(200);

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/user/edit-proc?id=1');
        $response->assertStatus(419);

        $response = $this->get('/admin/user/delete-confirm?id=1');
        $response->assertStatus(200);

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/user/delete-proc?id=1');
        $response->assertStatus(419);
    }

    /**
     * 一般ユーザーログイン
     */
    public function test_userAccess()
    {
        // 本番用としてチェックする（CSRFを有効にする）
        $this->app['env'] = 'production';
        $this->dummyUserLogin();

        $response = $this->get('/admin/user');
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');

        $response = $this->get('/admin/user/add');
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/user/add-proc');
        $response->assertStatus(419);

        $response = $this->get('/admin/user/edit?id=1');
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');

        $response = $this->get('/admin/user/edit?id=' . Auth::user()->id);
        $response->assertStatus(200);

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/user/edit-proc?id=1');
        $response->assertStatus(419);

        $response = $this->get('/admin/user/delete-confirm?id=1');
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/user/delete-proc?id=1');
        $response->assertStatus(419);

        // 自分のIDが指定された編集ページはアクセス可能
        $id = Auth::user()->id;
        $response = $this->get('/admin/user/edit?id=' . $id);
        $response->assertStatus(200);
    }

    /**
     * ユーザーの登録処理
     * 通常（成功）
     */
    public function test_addUser()
    {
        $this->dummyAdminLogin();

        // 登録処理（通常、管理者）
        $postData = [];
        $postData['user_name'] = 'phpAdd';
        $postData['email'] = $this->mail . '+phpadd01' . $this->domain;
        $postData['password'] = 'phptest';
        $postData['auth'] = 1;
        $response = $this->post('/admin/user/add-proc', $postData);
        $db = new Users();
        $id = $db->getUserLastID();
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/user/edit?id=' . $id);
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/user/edit?id=' . $id);
        $response
            ->assertSee($postData['user_name'])
            ->assertSee($postData['email'])
            ->assertSee(config('umekoset.auth.' . $postData['auth']));

        // 登録（通常、一般ユーザー）
        $postData = [];
        $postData['user_name'] = 'phpAdd02';
        $postData['email'] = $this->mail . '+phpadd02' . $this->domain;
        $postData['password'] = 'phptest';
        $postData['auth'] = 2;
        $response = $this->post('/admin/user/add-proc', $postData);
        $db = new Users();
        $id = $db->getUserLastID();
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/user/edit?id=' . $id);
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/user/edit?id=' . $id);
        $response
            ->assertSee($postData['user_name'])
            ->assertSee($postData['email'])
            ->assertSee(config('umekoset.auth.' . $postData['auth']));
    }

    /**
     * ユーザー登録のエラー処理
     * ユーザー名空
     * ユーザー名255文字以上
     * メールアドレス空
     * メールアドレス指定外
     * メールアドレス255文字以上
     * メールアドレス既に登録されている
     * パスワード空
     * パスワード255文字以上
     * パスワード半角英数字以外
     * 権限空
     * 権限指定外（1か2以外は無効）
     */
    public function test_addUserValid()
    {
        $this->dummyAdminLogin();
        // 異常系確認用エラーメールアドレス
        $errMail = $this->mail . '+phpErr' . $this->domain;
        // テストデータ登録
        Users::factory()->create([
            'user_name' => 'phpAdd',
            'email' => $this->mail . '+phpadd01' . $this->domain,
            'password' => 'phptest',
            'auth' => 1
        ]);

        // ユーザー名が空
        $postData = [];
        $postData['email'] = $errMail;
        $postData['password'] = 'phptest';
        $postData['auth'] = 1;

        $postData['user_name'] = '';
        $response = $this->post('/admin/user/add-proc', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['user_name' => '必須項目です。'])
            ->assertSessionDoesntHaveErrors(['email', 'password', 'auth']);

        // ユーザー名文字数
        $str = '';
        $str2 = '';
        for($i=1; $i<=256; $i++) {
            $str .= 'a';
            $str2 .= 'あ';
        }
        $postData['user_name'] = $str;
        $response = $this->post('/admin/user/add-proc', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['user_name' => '255字以内で入力してください。'])
            ->assertSessionDoesntHaveErrors(['email', 'password', 'auth']);

        $postData['user_name'] = $str2;
        $response = $this->post('/admin/user/add-proc', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['user_name' => '255字以内で入力してください。'])
            ->assertSessionDoesntHaveErrors(['email', 'password', 'auth']);

        // メールアドレス空
        $postData = [];
        $postData['user_name'] = 'Err';
        $postData['password'] = 'phptest';
        $postData['auth'] = 2;

        $postData['email'] = '';
        $response = $this->post('/admin/user/add-proc', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['email' => '必須項目です。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'password', 'auth']);

        // メールアドレス指定外
        $postData['email'] = 'aaa@example.com';
        $response = $this->post('/admin/user/add-proc', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['email' => '有効なメールアドレスではありません。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'password', 'auth']);

        $postData['email'] = 'test';
        $response = $this->post('/admin/user/add-proc', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['email' => '有効なメールアドレスではありません。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'password', 'auth']);

        // メールアドレス255文字以上
        $domain = '@example.com';
        $str = '';
        for($i=1; $i<=256-strlen($domain); $i++) {
            $str .= 'a';
        }
        $postData['email'] = $str . $domain;
        $response = $this->post('/admin/user/add-proc', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['email' => '255字以内で入力してください。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'password', 'auth']);

        // メールアドレス既に登録されている
        $postData['email'] = $this->mail . '+phpadd01' . $this->domain;
        $response = $this->post('/admin/user/add-proc', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['email' => '既に存在します。別の情報を入力してください。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'password', 'auth']);

        // パスワード空
        $postData = [];
        $postData['user_name'] = 'Err';
        $postData['email'] = $errMail;
        $postData['auth'] = 2;

        $postData['password'] = '';
        $response = $this->post('/admin/user/add-proc', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['password' => '必須項目です。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'email', 'auth']);

        // パスワード255文字以上
        for($i=1; $i<=256; $i++) {
            $str .= 'a';
        }
        $postData['password'] = $str;
        $response = $this->post('/admin/user/add-proc', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['password' => '255字以内で入力してください。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'email', 'auth']);

        // パスワード半角英数字以外
        $postData['password'] = '-012a';
        $response = $this->post('/admin/user/add-proc', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['password' => '半角英数字で入力してください。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'email', 'auth']);

        $postData['password'] = 'あいう';
        $response = $this->post('/admin/user/add-proc', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['password' => '半角英数字で入力してください。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'email', 'auth']);

        // 権限空
        $postData = [];
        $postData['user_name'] = 'Err';
        $postData['email'] = $errMail;
        $postData['password'] = 'phptest';

        $postData['auth'] = '';
        $response = $this->post('/admin/user/add-proc', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['auth' => '必須項目です。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'email', 'password']);

        // 権限指定外（1か2以外は無効）
        $postData['auth'] = 'あ';
        $response = $this->post('/admin/user/add-proc', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['auth' => '半角数字で入力してください。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'email', 'password']);

        $postData['auth'] = 3;
        $response = $this->post('/admin/user/add-proc', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['auth' => '不正な値が入力されています。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'email', 'password']);
    }

    /**
     * ユーザーの編集画面（管理者ログイン）
     * 正常
     */
    public function test_editAdminUser()
    {
        $this->dummyAdminLogin();

        // 編集 管理者
        $db = new Users();
        $user = $db->getUser(1);
        $response = $this->get('/admin/user/edit?id=1');
        $response
            ->assertStatus(200)
            ->assertSee($user->user_name)
            ->assertSee($user->email)
            ->assertSee(config('umekoset.auth.' . $user->auth));

        // 編集 一般ユーザー
        $db = new Users();
        $user = $db->getUser(2);
        $response = $this->get('/admin/user/edit?id=2');
        $response
            ->assertStatus(200)
            ->assertSee($user->user_name)
            ->assertSee($user->email)
            ->assertSee(config('umekoset.auth.' . $user->auth));
    }

    /**
     * ユーザーの編集画面（管理者ログイン）
     * ID空
     * IDが存在しない
     * IDが数値以外
     */
    public function test_editAdminUserValid()
    {
        $this->dummyAdminLogin();

        // IDが空
        $response = $this->get('/admin/user/edit?id=');
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');

        // IDが存在しない
        $response = $this->get('/admin/user/edit?id=999999');
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');

        // IDが数値以外
        $response = $this->get('/admin/user/edit?id=あ');
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');
    }

    /**
     * ユーザーの編集処理（管理者ログイン）
     * 通常（成功、パスワード変更なし）
     * 通常（成功、パスワード変更あり）
     */
    public function test_editAdminProcUser()
    {
        $this->dummyAdminLogin();
        // 編集するデータ
        $db = new Users();
        $user = $db->getUser(1);

        // 編集処理 管理者（通常、パスワード変更なし）
        $postData = [];
        $postData['user_name'] = 'phpEdit';
        $postData['email'] = $this->mail . '+phpedit' . $this->domain;
        $postData['password'] = '';
        $response = $this->post('/admin/user/edit-proc?id=1', $postData);
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/user/edit?id=1');
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/user/edit?id=1');
        $response
            ->assertSee($postData['user_name'])
            ->assertSee($postData['email'])
            ->assertSee(config('umekoset.auth.' . $user->auth));

        // 編集処理 管理者（通常、パスワード変更あり）
        $postData = [];
        $postData['user_name'] = 'phpEdit';
        $postData['email'] = $this->mail . '+phpedit' . $this->domain;
        $postData['password'] = 'phptest';
        $response = $this->post('/admin/user/edit-proc?id=1', $postData);
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/user/edit?id=1');
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/user/edit?id=1');
        $response
            ->assertSee($postData['user_name'])
            ->assertSee($postData['email'])
            ->assertSee(config('umekoset.auth.' . $user->auth));

        // 編集するデータ
        $db = new Users();
        $user = $db->getUser(2);

        // 編集処理 一般ユーザー（通常、パスワード変更なし）
        $postData = [];
        $postData['user_name'] = 'phpEdit2';
        $postData['email'] = $this->mail . '+phpedit2' . $this->domain;
        $postData['password'] = '';
        $response = $this->post('/admin/user/edit-proc?id=2', $postData);
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/user/edit?id=2');
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/user/edit?id=2');
        $response
            ->assertSee($postData['user_name'])
            ->assertSee($postData['email'])
            ->assertSee(config('umekoset.auth.' . $user->auth));

        // 編集処理 一般ユーザー（通常、パスワード変更あり）
        $postData = [];
        $postData['user_name'] = 'phpEdit2';
        $postData['email'] = $this->mail . '+phpedit2' . $this->domain;
        $postData['password'] = 'phptest';
        $response = $this->post('/admin/user/edit-proc?id=2', $postData);
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/user/edit?id=2');
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/user/edit?id=2');
        $response
            ->assertSee($postData['user_name'])
            ->assertSee($postData['email'])
            ->assertSee(config('umekoset.auth.' . $user->auth));
    }

    /**
     * ユーザー編集のエラー処理（管理者ログイン）
     * ID空
     * IDが存在しない
     * IDが数値以外
     * ユーザー名空
     * ユーザー名255文字以上
     * メールアドレス空
     * メールアドレス指定外
     * メールアドレス255文字以上
     * メールアドレス既に登録されている
     * パスワード空
     * パスワード255文字以上
     * パスワード半角英数字以外
     * 権限空
     * 権限指定外（1か2以外は無効）
     */
    public function test_editAdminProcUserValid()
    {

        $this->dummyAdminLogin();
        // 異常系確認用エラーメールアドレス
        $errMail = $this->mail . '+phpErr' . $this->domain;
        // テストデータ登録
        Users::factory()->create([
            'user_name' => 'phpAdd',
            'email' => $this->mail . '+phpadd01' . $this->domain,
            'password' => 'phptest',
            'auth' => 1
        ]);

        $postData = [];
        $postData['user_name'] = 'phpEdit';
        $postData['email'] = $errMail;
        $postData['password'] = 'phptest';

        // IDが空
        $response = $this->post('/admin/user/edit-proc?id=', $postData);
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');

        // IDが存在しない
        $response = $this->post('/admin/user/edit-proc?id=999999', $postData);
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');

        // IDが数値以外
        $response = $this->post('/admin/user/edit-proc?id=あ', $postData);
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');

        // ユーザー名が空
        $postData['user_name'] = '';
        $response = $this->post('/admin/user/edit-proc?id=1', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['user_name' => '必須項目です。'])
            ->assertSessionDoesntHaveErrors(['email', 'password']);

        // ユーザー名文字数
        $str = '';
        $str2 = '';
        for($i=1; $i<=256; $i++) {
            $str .= 'a';
            $str2 .= 'あ';
        }
        $postData['user_name'] = $str;
        $response = $this->post('/admin/user/edit-proc?id=1', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['user_name' => '255字以内で入力してください。'])
            ->assertSessionDoesntHaveErrors(['email', 'password']);

        $postData['user_name'] = $str2;
        $response = $this->post('/admin/user/edit-proc?id=1', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['user_name' => '255字以内で入力してください。'])
            ->assertSessionDoesntHaveErrors(['email', 'password']);

        // メールアドレス空
        $postData = [];
        $postData['user_name'] = 'Err';
        $postData['password'] = 'phptest';

        $postData['email'] = '';
        $response = $this->post('/admin/user/edit-proc?id=1', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['email' => '必須項目です。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'password']);

        // メールアドレス指定外
        $postData['email'] = 'aaa@example.com';
        $response = $this->post('/admin/user/edit-proc?id=1', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['email' => '有効なメールアドレスではありません。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'password']);

        $postData['email'] = 'test';
        $response = $this->post('/admin/user/edit-proc?id=1', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['email' => '有効なメールアドレスではありません。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'password']);

        // メールアドレス255文字以上
        $domain = '@example.com';
        $str = '';
        for($i=1; $i<=256-strlen($domain); $i++) {
            $str .= 'a';
        }
        $postData['email'] = $str . $domain;
        $response = $this->post('/admin/user/edit-proc?id=1', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['email' => '255字以内で入力してください。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'password']);

        // メールアドレス既に登録されている
        $postData['email'] = $this->mail . '+phpadd01' . $this->domain;
        $response = $this->post('/admin/user/edit-proc?id=1', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['email' => '既に存在します。別の情報を入力してください。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'password']);

        // パスワード255文字以上
        $postData = [];
        $postData['user_name'] = 'Err';
        $postData['email'] = $errMail;

        for($i=1; $i<=256; $i++) {
            $str .= 'a';
        }
        $postData['password'] = $str;
        $response = $this->post('/admin/user/edit-proc?id=1', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['password' => '255字以内で入力してください。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'email']);

        // パスワード半角英数字以外
        $postData['password'] = '-012a';
        $response = $this->post('/admin/user/edit-proc?id=1', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['password' => '半角英数字で入力してください。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'email']);

        $postData['password'] = 'あいう';
        $response = $this->post('/admin/user/edit-proc?id=1', $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['password' => '半角英数字で入力してください。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'email']);
    }

    /**
     * ユーザーの編集画面（ユーザーログイン）
     * 正常
     */
    public function test_editUser()
    {
        $this->dummyUserLogin();

        // 編集 一般ユーザー
        $id = Auth::user()->id;
        $db = new Users();
        $user = $db->getUser($id);
        $response = $this->get('/admin/user/edit?id=' . $id);
        $response
            ->assertStatus(200)
            ->assertSee($user->user_name)
            ->assertSee($user->email)
            ->assertSee(config('umekoset.auth.' . $user->auth));
    }

    /**
     * ユーザーの編集画面（ユーザーログイン）
     * ID空
     * IDが存在しない
     * IDが数値以外
     * 自分以外のユーザー
     */
    public function test_editUserValid()
    {
        $this->dummyUserLogin();

        // IDが空
        $response = $this->get('/admin/user/edit?id=');
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');

        // IDが存在しない
        $response = $this->get('/admin/user/edit?id=999999');
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');

        // IDが数値以外
        $response = $this->get('/admin/user/edit?id=あ');
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');

        // 自分以外のユーザー 管理者
        $response = $this->get('/admin/user/edit?id=1');
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');

        // 自分以外のユーザー 一般ユーザー
        $response = $this->get('/admin/user/edit?id=3');
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');
    }

    /**
     * ユーザーの編集処理（ユーザーログイン）
     * 通常（成功、パスワード変更なし）
     * 通常（成功、パスワード変更あり）
     */
    public function test_editProcUser()
    {
        $this->dummyUserLogin();
        $id = Auth::user()->id;

        // 編集するデータ
        $db = new Users();
        $user = $db->getUser($id);

        // 編集処理 ユーザー（通常、パスワード変更なし）
        $postData = [];
        $postData['user_name'] = 'phpEdit';
        $postData['email'] = $this->mail . '+phpedit' . $this->domain;
        $postData['password'] = '';
        $response = $this->post('/admin/user/edit-proc?id=' . $id, $postData);
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/user/edit?id=' . $id);
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/user/edit?id=' . $id);
        $response
            ->assertSee($postData['user_name'])
            ->assertSee($postData['email'])
            ->assertSee(config('umekoset.auth.' . $user->auth));

        // 編集処理 ユーザー（通常、パスワード変更あり）
        $postData = [];
        $postData['user_name'] = 'phpEdit';
        $postData['email'] = $this->mail . '+phpedit' . $this->domain;
        $postData['password'] = 'phptest';
        $response = $this->post('/admin/user/edit-proc?id=' . $id, $postData);
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/user/edit?id=' . $id);
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/user/edit?id=' . $id);
        $response
            ->assertSee($postData['user_name'])
            ->assertSee($postData['email'])
            ->assertSee(config('umekoset.auth.' . $user->auth));
    }

    /**
     * ユーザー編集のエラー処理（ユーザーログイン）
     * ID空
     * IDが存在しない
     * IDが数値以外
     * 自分以外のユーザー
     * ユーザー名空
     * ユーザー名255文字以上
     * メールアドレス空
     * メールアドレス指定外
     * メールアドレス255文字以上
     * メールアドレス既に登録されている
     * パスワード空
     * パスワード255文字以上
     * パスワード半角英数字以外
     * 権限空
     * 権限指定外（1か2以外は無効）
     */
    public function test_editProcUserValid()
    {
        $this->dummyUserLogin();
        $id = Auth::user()->id;

        // 異常系確認用エラーメールアドレス
        $errMail = $this->mail . '+phpErr' . $this->domain;
        // テストデータ登録
        Users::factory()->create([
            'user_name' => 'phpAdd',
            'email' => $this->mail . '+phpadd01' . $this->domain,
            'password' => 'phptest',
            'auth' => 2
        ]);

        $postData = [];
        $postData['user_name'] = 'phpEdit';
        $postData['email'] = $errMail;
        $postData['password'] = 'phptest';

        // IDが空
        $response = $this->post('/admin/user/edit-proc?id=', $postData);
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');

        // IDが存在しない
        $response = $this->post('/admin/user/edit-proc?id=999999', $postData);
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');

        // IDが数値以外
        $response = $this->post('/admin/user/edit-proc?id=あ', $postData);
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');

        // 自分以外のユーザー
        $response = $this->post('/admin/user/edit-proc?id=1', $postData);
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');

        // 自分以外のユーザー
        $response = $this->post('/admin/user/edit-proc?id=3', $postData);
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');

        // ユーザー名が空
        $postData['user_name'] = '';
        $response = $this->post('/admin/user/edit-proc?id=' . $id, $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['user_name' => '必須項目です。'])
            ->assertSessionDoesntHaveErrors(['email', 'password']);

        // ユーザー名文字数
        $str = '';
        $str2 = '';
        for($i=1; $i<=256; $i++) {
            $str .= 'a';
            $str2 .= 'あ';
        }
        $postData['user_name'] = $str;
        $response = $this->post('/admin/user/edit-proc?id=' . $id, $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['user_name' => '255字以内で入力してください。'])
            ->assertSessionDoesntHaveErrors(['email', 'password']);

        $postData['user_name'] = $str2;
        $response = $this->post('/admin/user/edit-proc?id=' . $id, $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['user_name' => '255字以内で入力してください。'])
            ->assertSessionDoesntHaveErrors(['email', 'password']);

        // メールアドレス空
        $postData = [];
        $postData['user_name'] = 'Err';
        $postData['password'] = 'phptest';

        $postData['email'] = '';
        $response = $this->post('/admin/user/edit-proc?id=' . $id, $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['email' => '必須項目です。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'password']);

        // メールアドレス指定外
        $postData['email'] = 'aaa@example.com';
        $response = $this->post('/admin/user/edit-proc?id=' . $id, $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['email' => '有効なメールアドレスではありません。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'password']);

        $postData['email'] = 'test';
        $response = $this->post('/admin/user/edit-proc?id=' . $id, $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['email' => '有効なメールアドレスではありません。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'password']);

        // メールアドレス255文字以上
        $domain = '@example.com';
        $str = '';
        for($i=1; $i<=256-strlen($domain); $i++) {
            $str .= 'a';
        }
        $postData['email'] = $str . $domain;
        $response = $this->post('/admin/user/edit-proc?id=' . $id, $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['email' => '255字以内で入力してください。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'password']);

        // メールアドレス既に登録されている
        $postData['email'] = $this->mail . '+phpadd01' . $this->domain;
        $response = $this->post('/admin/user/edit-proc?id=' . $id, $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['email' => '既に存在します。別の情報を入力してください。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'password']);

        // パスワード255文字以上
        $postData = [];
        $postData['user_name'] = 'Err';
        $postData['email'] = $errMail;

        for($i=1; $i<=256; $i++) {
            $str .= 'a';
        }
        $postData['password'] = $str;
        $response = $this->post('/admin/user/edit-proc?id=' . $id, $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['password' => '255字以内で入力してください。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'email']);

        // パスワード半角英数字以外
        $postData['password'] = '-012a';
        $response = $this->post('/admin/user/edit-proc?id=' . $id, $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['password' => '半角英数字で入力してください。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'email']);

        $postData['password'] = 'あいう';
        $response = $this->post('/admin/user/edit-proc?id=' . $id, $postData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['password' => '半角英数字で入力してください。'])
            ->assertSessionDoesntHaveErrors(['user_name', 'email']);
    }

    /**
     * ユーザーの削除確認
     * 正常
     */
    public function test_deleteUser()
    {
        $this->dummyAdminLogin();

        $response = $this->get('/admin/user/delete-confirm?id=1');
        $response->assertStatus(200);
    }

    /**
     * ユーザーの削除確認
    * 異常
    * IDなし
    * 存在しないID
    * ID数字以外
    * ログイン中のユーザー
    */
    public function test_deleteUserValid()
    {
        $this->dummyAdminLogin();

        // IDなし
        $response = $this->get('/admin/user/delete-confirm?id=');
        $response
        ->assertStatus(302)
        ->assertRedirect('/admin/top');

        // 存在しないID
        $response = $this->get('/admin/user/delete-confirm?id=99999');
            $response
                ->assertStatus(302)
                ->assertRedirect('/admin/top');

        // ID数字以外
        $response = $this->get('/admin/user/delete-confirm?id=い');
            $response
                ->assertStatus(302)
                ->assertRedirect('/admin/top');

        // ログイン中のユーザー
        $response = $this->get('/admin/user/delete-confirm?id=' . Auth::user()->id);
            $response
                ->assertStatus(302)
                ->assertRedirect('/admin/top');
    }

     /**
     * ユーザーの削除処理
     * 通常（成功）
     */
    public function test_deleteProc()
    {
        $this->dummyAdminLogin();

        $postData= [];
        // 管理者削除
        $postData['id'] = 1;
        $response = $this->post('/admin/user/delete-proc', $postData);
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/user');
        // 削除されたユーザーにアクセスできないことを確認
        $response = $this->get('/admin/user/edit?id=' . $postData['id']);
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');

        $postData['id'] = 3;
        // ユーザー削除
        $response = $this->post('/admin/user/delete-proc', $postData);
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/user');
        // 削除されたユーザーにアクセスできないことを確認
        $response = $this->get('/admin/user/edit?id=' . $postData['id']);
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');
    }

    /**
     * ユーザーの削除処理
     * IDなし
     * 存在しないID
     * ID数字以外
     * ログイン中のユーザー
     */
    public function test_deleteProcValid()
    {
        $this->dummyAdminLogin();

        $postData= [];
        $postData['id'] = '';
        // IDなし
        $response = $this->post('/admin/user/delete-proc', $postData);
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');

        // 存在しないID
        $postData['id'] = 99999;
        $response = $this->post('/admin/user/delete-proc', $postData);
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');

        // ID数字以外
        $postData['id'] = 'う';
        $response = $this->post('/admin/user/delete-proc', $postData);
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');

        // ログイン中のユーザー
        $postData['id'] = Auth::user()->id;
        $response = $this->post('/admin/user/delete-proc', $postData);
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/top');
    }

    /**
     * 管理者ダミーユーザーログイン
     */
    private function dummyAdminLogin()
    {
        $user = Users::factory()->create();
        return $this->actingAs($user)
            ->withSession(['email' => $user->email])
            ->get('/admin/top'); // リダイレクト
    }

    /**
     * 一般ユーザーダミーログイン
     */
    private function dummyUserLogin()
    {
        $user = Users::factory()->create([
            'auth' => 2
        ]);
        return $this->actingAs($user)
            ->withSession(['email' => $user->email])
            ->get('/admin/top'); // リダイレクト
    }
}
