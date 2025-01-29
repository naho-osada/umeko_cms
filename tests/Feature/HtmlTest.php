<?php
/**
 * HTML生成機能の試験
 */
namespace Tests\Feature;

use App\Models\Users;
use App\Models\Article;
use App\Models\RelatedCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class HtmlTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    /**
     * HTML生成ページに認証が必要なことを確認する
     */
    public function test_denyAccess()
    {
        $response = $this->get('/admin/html');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');

        $response = $this->post('/admin/html/make');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');
    }

    /**
     * HTML生成
     * 管理者ログイン
     */
    public function test_adminAccess()
    {
        // 本番用としてチェックする（CSRFを有効にする）
        $this->app['env'] = 'production';
        $this->dummyAdminLogin();

        $response = $this->get('/admin/html');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.html.index');

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/html/make');
        $response->assertStatus(419);
    }

    /**
     * HTML生成
     * 一般ユーザー
     */
    public function test_userAccess()
    {
        // 本番用としてチェックする（CSRFを有効にする）
        $this->app['env'] = 'production';
        $this->dummyUserLogin();

        $response = $this->get('/admin/html');
        $response
            ->assertStatus(302);

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/html/make');
        $response->assertStatus(419);
    }

    /**
     * HTML表示ページ
     */
    public function test_viewHtml()
    {
        // 管理者
        $this->dummyAdminLogin();
        // トップページ
        $response = $this->get('/admin/html');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.html.index')
            ->assertSee('<h2>HTML生成する記事一覧</h2>', false)
            ->assertSee('出力先のドメイン名', false)
            ->assertSee('<button class="btn submit submit-btn" id="html-maker" type="submit">HTML生成</button>', false)
            ->assertSee('<div class="list-html">', false)
            ->assertSee('<a href="/">トップページ</a>', false);
    }

    /**
     * HTML生成
     */
    public function test_htmlMake()
    {
        // ドメイン指定空で実行
        $postData = [];
        $postData['domain'] = '';
        $response = $this->post('/admin/html/make', $postData);
        $response->assertStatus(302);
        $this->assertDirectoryExists('storage/app/html-maker');
        $this->assertFileExists('storage/app/html.zip');

        $indexFile = __DIR__ . '/../../storage/app/html-maker/index.html';
        // 文字列の確認
        $index = file_get_contents($indexFile);
        $this->assertMatchesRegularExpression('/<html/', $index);
        $this->assertMatchesRegularExpression('/\/html>/', $index);

        $postData = [];
        $postData['domain'] = 'http://localhost';
        $response = $this->post('/admin/html/make', $postData);
        $response->assertStatus(302);
        $this->assertDirectoryExists('storage/app/html-maker');
        $this->assertFileExists('storage/app/html.zip');
        $indexFile = __DIR__ . '/../../storage/app/html-maker/index.html';
        // 文字列の確認
        $index = file_get_contents($indexFile);
        $this->assertMatchesRegularExpression('/<html/', $index);
        $this->assertMatchesRegularExpression('/\/html>/', $index);
    }

    // HTML生成
    // 異常系 文字数256文字以上のみ
    public function test_ErrHtmlMake()
    {
        // ドメインの文字数
        $postData = [];
        $str = '';
        for($i=0; $i<=256; $i++) {
            $str .= 'a';
        }
        $postData['domain'] = $str;
        $response = $this->post('/admin/html/make', $postData);
        $response->assertStatus(302);
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
