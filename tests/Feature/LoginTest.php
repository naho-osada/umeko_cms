<?php
// vendor/bin/phpunit
namespace Tests\Feature;

use App\Models\Users;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase; // テスト用データを自動で元に戻す
use Illuminate\Support\Facades\Auth;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected $errMiss = 'ログインに失敗しました。';
    protected $errReq = '必須項目です。';

    /**
     * ログイン画面を表示
     */
    public function testLoginView()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        // 認証されていないことを確認
        $this->assertGuest();
    }

    /**
     * 管理トップページアクセス（ログイン画面へリダイレクト）
     */
    public function testNonloginAccess()
    {
        $response = $this->get('/admin/top');
        $response->assertStatus(302)
                 ->assertRedirect('/login'); // リダイレクト先を確認
        // 認証されていないことを確認
        $this->assertGuest();
    }

    /**
     * ログイン処理を実行
     */
    public function testLogin()
    {
        // 認証されていないことを確認
        $this->assertGuest();
        // ダミーログイン
        $response = $this->dummyLogin();
        $response->assertStatus(200);
        // 認証を確認
        $this->assertAuthenticated();
    }

    /**
     * 指定のユーザーのメールアドレスでログインを実行
     */
    public function testEmailLogin()
    {
        $user = Users::factory()->create([
            'password'  => bcrypt('test1111')
        ]);

        $userParam = [];
        $userParam['email'] = $user->email;
        $userParam['password'] = 'test1111';
        $response = $this->post('login', $userParam);
        $response->assertStatus(302)
                 ->assertRedirect('/admin/top'); // リダイレクト先を確認
        // 認証を確認
        $this->assertTrue(Auth::check());
    }

    /**
     * 異常系：情報が空
     */
    public function testErrEmpty()
    {
        $userParam = [];
        $userParam['email'] = '';
        $userParam['password'] = '';
        $response = $this->post('login', $userParam);
        $response->assertStatus(302);
        // 認証を確認
        $this->assertGuest();

        // エラーメッセージを確認
        $response->assertSessionHasErrors(['email' => $this->errReq]);
    }

    /**
     * 異常系：メールアドレスが空
     */
    public function testErrEmptyUser()
    {
        $user = Users::factory()->create([
            'password'  => bcrypt('test1111')
        ]);

        $userParam = [];
        $userParam['email'] = '';
        $userParam['password'] = 'test1111';
        $response = $this->post('login', $userParam);
        $response->assertStatus(302);
        // 認証を確認
        $this->assertGuest();

        // エラーメッセージを確認
        $response->assertSessionHasErrors(['email' => $this->errReq]);
    }

    /**
     * 異常系：メールアドレスまたはユーザーIDが不一致
     */
    public function testErrUser()
    {
        $user = Users::factory()->create([
            'password'  => bcrypt('test1111')
        ]);

        $userParam = [];
        $userParam['email'] = 'aaa';
        $userParam['password'] = 'test1111';
        $response = $this->post('login', $userParam);
        $response->assertStatus(302);
        // 認証を確認
        $this->assertGuest();

        // エラーメッセージを確認
        $response->assertSessionHasErrors(['email' => $this->errMiss]);
    }

    /**
     * 異常系：パスワードが空
     */
    public function testErrEmptyPass()
    {
        $user = Users::factory()->create([
            'password'  => bcrypt('test1111')
        ]);

        $userParam = [];
        $userParam['email'] = $user->email;
        $userParam['password'] = '';
        $response = $this->post('login', $userParam);
        $response->assertStatus(302);
        // 認証を確認
        $this->assertGuest();

        // エラーメッセージを確認
        $response->assertSessionHasErrors(['password' => $this->errReq]);
    }

    /**
     * 異常系：パスワードが不一致
     */
    public function testErrPass()
    {
        $user = Users::factory()->create([
            'password'  => bcrypt('test1111')
        ]);

        $userParam = [];
        $userParam['email'] = $user->email;
        $userParam['password'] = 'test';
        $response = $this->post('login', $userParam);
        $response->assertStatus(302);
        // 認証を確認
        $this->assertGuest();

        // エラーメッセージを確認
        $response->assertSessionHasErrors(['email' => $this->errMiss]);
    }

    /**
     * 異常系：ユーザーIDまたはメールアドレスとパスワードが不一致
     */
    public function testErrLogin()
    {
        $user = Users::factory()->create([
            'password'  => bcrypt('test1111')
        ]);

        $userParam = [];
        $userParam['email'] = $user->email . 'test';
        $userParam['password'] = 'test';
        $response = $this->post('login', $userParam);
        $response->assertStatus(302);
        // 認証を確認
        $this->assertGuest();

        // エラーメッセージを確認
        $response->assertSessionHasErrors(['email' => $this->errMiss]);
    }

    /**
     * ログアウト処理を実行
     */
    public function testLogout()
    {
        // ダミーログイン
        $response = $this->dummyLogin();
        // 認証を確認
        $this->assertAuthenticated();
        $response = $this->post('/logout'); // リダイレクト先を確認
        // ホーム画面にリダイレクト
        $response->assertStatus(302)
                 ->assertRedirect('/login');
        // 認証されていないことを確認
        $this->assertGuest();
    }

    /**
     * ダミーユーザーログイン
     */
    private function dummyLogin()
    {
        $user = Users::factory()->create();
        return $this->actingAs($user)
                    ->withSession(['email' => $user->email])
                    ->get('/admin/top'); // リダイレクト
    }
}