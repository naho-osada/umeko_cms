<?php
/**
 * ファイル一覧、編集機能の試験
 */
namespace Tests\Feature;

use App\Models\Users;
use App\Models\SaveFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class FileTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    /**
     * ファイル関係のページに認証が必要なことを確認する
     */
    public function test_denyFile()
    {
        $response = $this->get('/admin/file');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');

        $response = $this->get('/admin/file/delete-confirm?id=1');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');

        $response = $this->post('/admin/file/delete-proc?id=1');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');

        $response = $this->get('/admin/file/edit');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');

        $response = $this->get('/admin/file/edit?id=1');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');
    }

    /**
     * ファイル一覧、編集、削除アクセス
     * 管理者ログイン
     */
    public function test_adminAccess()
    {
        // 本番用としてチェックする（CSRFを有効にする）
        $this->app['env'] = 'production';
        $this->dummyAdminLogin();

        $response = $this->get('/admin/file');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.file.index');

        $response = $this->get('/admin/file/edit');
        $response->assertStatus(302);

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/file/edit-proc');
        $response->assertStatus(419);

        $response = $this->get('/admin/file/edit?id=1');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.file.edit');

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/file/edit-proc?id=1');
        $response->assertStatus(419);

        $response = $this->get('/admin/file/delete-confirm?id=1');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.file.delete-confirm');

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/file/delete-proc?id=1');
        $response->assertStatus(419);
    }

    /**
     * ファイル一覧、編集、削除アクセス
     * 一般ユーザー
     */
    public function test_userAccess()
    {
        // 本番用としてチェックする（CSRFを有効にする）
        $this->app['env'] = 'production';
        $this->dummyUserLogin();

        $response = $this->get('/admin/file');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.file.index');

        $response = $this->get('/admin/file/edit');
        $response->assertStatus(302);

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/file/edit-proc');
        $response->assertStatus(419);

        // 自分で作成したファイル以外は編集できないが、閲覧のみ可能
        $response = $this->get('/admin/file/edit?id=1');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.file.edit')
            ->assertSee('/storage/uploads/image/2021/10/middle/test.jpg "></div>', false)
            ->assertSee('<dt>登録者</dt><dd>管理者</dd>', false)
            ->assertSee('<dt>登録日</dt>', false)
            ->assertSeeText('test01 description', false)
            ->assertSee('/admin/file">戻る</a></button</div>', false) // 戻るボタンがある
            ->assertDontSee('<input name="description" type="text" value="test01 description">', false) // 入力ボックスはない
            ->assertDontSee('<div class="submit-btn"><button class="btn submit" type="submit">更新</button></div>', false) // 行進ボタンはない
            ->assertDontSee('<form method="post" action="', false);

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/file/edit-proc?id=1');
        $response->assertStatus(419);

        // 削除不可
        $response = $this->get('/admin/file/delete-confirm?id=1');
        $response->assertStatus(302);

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/file/delete-proc?id=1');
        $response->assertStatus(419);
    }

    /**
     * ファイル一覧の表示
     * 通常（成功）
     */
    public function test_indexFile()
    {
        // 管理者
        $this->dummyAdminLogin();
        $response = $this->get('/admin/file');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.file.index')
            ->assertSee('/storage/uploads/image/2021/10/small/test.jpg" alt="test01 description">', false)
            ->assertSee('<li class="file-description">test01 description</li>', false)
            ->assertSee('<div class="edit-btn btn">', false)
            ->assertSee('<div class="delete-btn btn">', false)
            ->assertDontSee('<div class="private-btn btn">', false) // 詳細ボタン
            ->assertDontSee('<div class="result-msg">表示する情報がありません。', false);

        // ユーザー
        $this->dummyUserLogin();
        $response = $this->get('/admin/file');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.file.index')
            ->assertSee('<li class="file-description">test01 description</li>', false)
            ->assertDontSee('<div class="edit-btn btn">', false) // 自分で作成したものしか編集できない
            ->assertDontSee('<div class="delete-btn btn">', false) // ユーザーは削除不可
            ->assertSee('<div class="private-btn btn">', false) // 詳細ボタン
            ->assertDontSee('<div class="result-msg">表示する情報がありません。', false);
    }

    /**
     * ファイル一覧からの削除確認
     * 通常（成功）
     */
    public function test_deleteConfirmFile()
    {
        $this->dummyAdminLogin();
        $response = $this->get('/admin/file/delete-confirm?id=1');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.file.delete-confirm')
            ->assertSee('<dt>ファイル</dt>', false)
            ->assertSee('/storage/uploads/image/2021/10/middle/test.jpg "></div>', false)
            ->assertSeeText('test.jpg', false)
            ->assertSee('<dt>登録者</dt><dd>管理者</dd>', false)
            ->assertSee('<dt>登録日</dt>', false)
            ->assertSee('<dt>説明文</dt><dd>test01 description</dd>', false)
            ->assertSee('<h2>この画像が使われている記事一覧</h2>', false)
            ->assertSee('<div class="post_title"><span class="publish-btn disp-status">アイキャッチ画像</span></div>', false)
            ->assertSee('test04" target="_blank" rel="noopener noreferrer">画像確認用01</a></div>', false)
            ->assertSee('<div class="post_title"><span class="publish-btn disp-status">本文で使用</span></div>', false)
            ->assertSee('test08" target="_blank" rel="noopener noreferrer">画像確認用05</a></div>', false);
    }
    /**
     * ファイル一覧からの削除確認（異常系）
     */
    public function test_deleteConfirmFileErr()
    {
        $this->dummyAdminLogin();
        // ID空（ファイル一覧リダイレクト）
        $response = $this->get('/admin/file/delete-confirm?id=');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');

        // 存在しないID
        $response = $this->get('/admin/file/delete-confirm?id=9999999');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');

        // 数値以外
        $response = $this->get('/admin/file/delete-confirm?id=aaa');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');
    }
    /**
     * ファイル一覧からの削除
     * 通常（成功）
     */
    public function test_deleteProcFile()
    {
        $this->dummyAdminLogin();
        $response = $this->post('/admin/file/delete-proc?id=1');
        $response
            ->assertStatus(302)
            ->assertSessionHas('flashmessage', 'ファイルを削除しました。')
            ->assertDontSeeText('カテゴリ01');

        // データが残っていないことを確認する
        $db = new SaveFile();
        $data = $db->getFile(1);
        $test = '';
        if(isset($data[0])) $test = $data;
        $this->assertSame('', $test);
    }
    /**
     * ファイル一覧からの削除（異常系）
     */
    public function test_deleteProcFileErr()
    {
        $this->dummyAdminLogin();
        // ID空（ファイル一覧リダイレクト）
        $response = $this->post('/admin/file/delete-proc?id=');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');

        // 存在しないID
        $response = $this->post('/admin/file/delete-proc?id=9999999');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');

        // 数値以外
        $response = $this->post('/admin/file/delete-proc?id=aaa');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');

        // ユーザーは削除処理不可
        $this->dummyUserLogin();
        $response = $this->post('/admin/file/delete-proc?id=1');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');
    }

    /**
     * ファイル編集画面
     * 通常（成功）
     */
    public function test_editFile()
    {
        // 管理者が投稿する
        $this->dummyAdminLogin();

        $id = 5;
        $description = 'ファイル更新テスト';

        $updData = [];
        $updData['id'] = $id;
        $updData['description'] = $description;
        $response = $this->post('/admin/file/edit-proc?id=' . $id, $updData);
        $response->assertStatus(302);

        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/file/edit?id=' . $id);
        $response
            ->assertSee('/storage/uploads/image/2021/10/middle/test05.jpg "></div>', false)
            ->assertSee('<dt>登録者</dt><dd>一般ユーザー04</dd>', false)
            ->assertSee('<dt>登録日</dt>', false)
            ->assertSee('<input name="description" type="text" value="' . $description . '">', false);
    }
    /**
     * ファイル編集画面 編集エラー
     */
    public function test_editFileErr()
    {
        $this->dummyAdminLogin();

        $description = 'ファイル更新テスト';

        $updData = [];
        $updData['description'] = $description;

        // 存在しないID
        $response = $this->post('/admin/file/edit-proc?id=999', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');

        // ID指定外
        $response = $this->post('/admin/file/edit-proc?id=aaa', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');

        $this->dummyUserLogin();
        $id = 5;
        $description = 'ファイル更新テスト';

        $updData = [];
        $updData['id'] = $id;
        $updData['description'] = $description;
        // ユーザーは編集不可
        $response = $this->post('/admin/file/edit-proc?id=' . $id, $updData);
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');
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
