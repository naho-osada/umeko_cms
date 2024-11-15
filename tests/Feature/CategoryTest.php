<?php
/**
 * カテゴリ一覧、登録機能の試験
 */
namespace Tests\Feature;

use App\Models\Users;
use App\Models\Category;
use App\Models\RelatedCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    /**
     * カテゴリ関係のページに認証が必要なことを確認する
     */
    public function test_denyCategory()
    {
        $response = $this->get('/admin/category');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');

        $response = $this->get('/admin/category/delete-confirm?id=1');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');

        $response = $this->post('/admin/category/delete-proc?id=1');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');

        $response = $this->get('/admin/category/edit');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');

        $response = $this->get('/admin/category/edit?id=1');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');
    }

    /**
     * カテゴリ一覧、編集、削除アクセス
     * 管理者ログイン
     */
    public function test_adminAccess()
    {
        // 本番用としてチェックする（CSRFを有効にする）
        $this->app['env'] = 'production';
        $this->dummyAdminLogin();

        $response = $this->get('/admin/category');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.category.index');

        $response = $this->get('/admin/category/edit');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.category.edit');

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/category/edit-proc');
        $response->assertStatus(419);

        $response = $this->get('/admin/category/edit?id=1');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.category.edit');

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/category/edit-proc?id=1');
        $response->assertStatus(419);

        $response = $this->get('/admin/category/delete-confirm?id=1');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.category.delete-confirm');

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/category/delete-proc?id=1');
        $response->assertStatus(419);
    }

    /**
     * カテゴリー一覧、編集、削除アクセス
     * 一般ユーザー
     */
    public function test_userAccess()
    {
        // 本番用としてチェックする（CSRFを有効にする）
        $this->app['env'] = 'production';
        $this->dummyUserLogin();

        $response = $this->get('/admin/category');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.category.index');

        $response = $this->get('/admin/category/edit');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.category.edit');

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/category/edit-proc');
        $response->assertStatus(419);

        // 自分で作成したカテゴリー以外は編集できない
        $response = $this->get('/admin/category/edit?id=1');
        $response->assertStatus(302);

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/category/edit-proc?id=1');
        $response->assertStatus(419);

        // 削除不可
        $response = $this->get('/admin/category/delete-confirm?id=1');
        $response->assertStatus(302);

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/category/delete-proc?id=1');
        $response->assertStatus(419);
    }

    /**
     * カテゴリー一覧の表示
     * 通常（成功）
     */
    public function test_searchCategory()
    {
        // 管理者
        $this->dummyAdminLogin();
        $response = $this->get('/admin/category');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.category.index')
            ->assertSee('<li><div class="category_name">', false)
            ->assertSee('<li><div class="edit-btn btn">', false)
            ->assertSee('<li><div class="delete-btn btn">', false)
            ->assertSeeText('カテゴリ01（ 1 ）')
            ->assertDontSee('<div class="result-msg">表示する情報がありません。', false);


        // ユーザー
        $this->dummyUserLogin();
        $response = $this->get('/admin/category');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.category.index')
            ->assertSee('<li><div class="category_name">', false)
            ->assertDontSee('<li><div class="edit-btn btn">', false) // 自分で作成したものしか編集できない
            ->assertDontSee('<li><div class="delete-btn btn">', false) // ユーザーは削除不可
            ->assertDontSee('<div class="result-msg">表示する情報がありません。', false);
    }

    /**
     * カテゴリー一覧からの削除確認
     * 通常（成功）
     */
    public function test_deleteConfirmCCategory()
    {
        $this->dummyAdminLogin();
        $response = $this->get('/admin/category/delete-confirm?id=1');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.category.delete-confirm')
            ->assertSee('<dt>カテゴリー表示名</dt>', false)
            ->assertSee('<dd class="article_parts">カテゴリ01</dd>', false)
            ->assertSee('<dt>カテゴリー名</dt>', false)
            ->assertSee('<dd class="article_parts">01</dd>', false)
            ->assertSee('<dt>ソート順</dt>', false)
            ->assertSee('<dd class="article_parts">1</dd>', false)
            ->assertSee('<dt>最終更新日時</dt>', false)
            ->assertSee('<h2>このカテゴリーを使っている記事一覧</h2>', false)
            ->assertSeeText('テスト投稿タイトル01');
    }
    /**
     * カテゴリー一覧からの削除確認（異常系）
     */
    public function test_deleteConfirmCategoryErr()
    {
        $this->dummyAdminLogin();
        // ID空（カテゴリー一覧リダイレクト）
        $response = $this->get('/admin/category/delete-confirm?id=');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');

        // 存在しないID
        $response = $this->get('/admin/category/delete-confirm?id=9999999');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');

        // 数値以外
        $response = $this->get('/admin/category/delete-confirm?id=aaa');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');
    }
    /**
     * カテゴリー一覧からの削除
     * 通常（成功）
     */
    public function test_deleteProcCategory()
    {
        $this->dummyAdminLogin();
        $response = $this->post('/admin/category/delete-proc?id=1');
        $response
            ->assertStatus(302)
            ->assertSessionHas('flashmessage', 'カテゴリーを削除しました。')
            ->assertDontSeeText('カテゴリ01');

        // related_categoryにデータが残っていないことを確認する
        $db = new RelatedCategory();
        $relCat = $db->getRelCatArticle(1);
        $this->assertSame([], $relCat);
    }
    /**
     * カテゴリー一覧からの削除（異常系）
     */
    public function test_deleteProcCategoryErr()
    {
        $this->dummyAdminLogin();
        // ID空（カテゴリー一覧リダイレクト）
        $response = $this->post('/admin/category/delete-proc?id=');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');

        // 存在しないID
        $response = $this->post('/admin/category/delete-proc?id=9999999');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');

        // 数値以外
        $response = $this->post('/admin/category/delete-proc?id=aaa');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');

        // ユーザーは削除処理不可
        $this->dummyUserLogin();
        $response = $this->post('/admin/category/delete-proc?id=1');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');
    }

    /**
     * カテゴリー投稿画面 新規
     * 通常（成功）
     */
    public function test_addCategory()
    {
        // 管理者が投稿する
        $this->dummyAdminLogin();

        $dispName = 'カテゴリー登録テスト';
        $categoryName = 'FeatureTestCategory';
        $sort = 10;
        $user_id =  Auth::user()->id;

        $updData = [];
        $updData['disp_name'] = $dispName;
        $updData['category_name'] = $categoryName;
        $updData['sort_no'] = $sort;
        $updData['user_id'] = $user_id;
        $response = $this->post('/admin/category/edit-proc', $updData);
        $response->assertStatus(302);

        $db = new Category();
        $id = $db->getCategoryLastID();
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/category/edit?id=' . $id);
        $response
            ->assertSee('<dd><input name="category_name" type="text" value="' . $categoryName . '"></dd>', false)
            ->assertSee('<dd><input name="disp_name" type="text" value="' . $dispName . '"></dd>', false)
            ->assertSee('<option value="' . $sort . '"  selected >' . $sort . '</option>', false);

        // ユーザーが投稿する
        $this->dummyUserLogin();
        $dispName = 'カテゴリー登録テスト02';
        $categoryName = 'FeatureTestCategory02';
        $sort = 5;
        $user_id = Auth::user()->id;

        $updData = [];
        $updData['disp_name'] = $dispName;
        $updData['category_name'] = $categoryName;
        $updData['sort_no'] = $sort;
        $updData['user_id'] = $user_id;
        $response = $this->post('/admin/category/edit-proc', $updData);
        $response->assertStatus(302);

        $db = new Category();
        $id = $db->getCategoryLastID();
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/category/edit?id=' . $id);
        $response
            ->assertSee('<dd><input name="category_name" type="text" value="' . $categoryName . '"></dd>', false)
            ->assertSee('<dd><input name="disp_name" type="text" value="' . $dispName . '"></dd>', false)
            ->assertSee('<option value="' . $sort . '"  selected >' . $sort . '</option>', false);
    }
    /**
     * カテゴリー投稿処理 新規投稿エラー
     */
    public function test_addCategoryErr()
    {
        $this->dummyAdminLogin();
        $str50 = '';
        for($i=1; $i<51; $i++) {
            $str50 .= 'あ';
        }
        $str50En = '';
        for($i=1; $i<51; $i++) {
            $str50En .= 'a';
        }

        // カテゴリー表示名 必須
        $dispName = 'カテゴリー登録テスト';
        $categoryName = 'FeatureTestCategory';
        $sort = 10;
        $user_id = Auth::user()->id;
        $updData = [];
        $updData['disp_name'] = '';
        $updData['category_name'] = $categoryName;
        $updData['sort_no'] = $sort;
        $updData['user_id'] = $user_id;
        $response = $this->post('/admin/category/edit-proc', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['disp_name' => '必須項目です。']);

        // カテゴリー表示名 50文字（OK）
        $updData = [];
        $updData['disp_name'] = $str50;
        $updData['category_name'] = $categoryName;
        $updData['sort_no'] = $sort;
        $updData['user_id'] = $user_id;
        $response = $this->post('/admin/category/edit-proc', $updData);
        $db = new Category();
        $id = $db->getCategoryLastID();
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/category/edit?id=' . $id);
        $response
            ->assertSee('<dd><input name="category_name" type="text" value="' . $categoryName . '"></dd>', false)
            ->assertSee('<dd><input name="disp_name" type="text" value="' . $str50 . '"></dd>', false)
            ->assertSee('<option value="' . $sort . '"  selected >' . $sort . '</option>', false);

        $categoryName = $categoryName . 'ABC';
        // カテゴリー表示名 51文字
        $updData = [];
        $updData['disp_name'] = $str50 . 'い';
        $updData['category_name'] = $categoryName;
        $updData['sort_no'] = $sort;
        $updData['user_id'] = $user_id;
        $response = $this->post('/admin/category/edit-proc', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['disp_name' => '50字以内で入力してください。']);

        // カテゴリー名 必須
        $updData = [];
        $updData['disp_name'] = $dispName;
        $updData['category_name'] = '';
        $updData['sort_no'] = $sort;
        $updData['user_id'] = $user_id;
        $response = $this->post('/admin/category/edit-proc', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['category_name' => '必須項目です。']);

        // カテゴリー名 50文字（OK）
        $updData = [];
        $updData['disp_name'] = $dispName;
        $updData['category_name'] = $str50En;
        $updData['sort_no'] = $sort;
        $updData['user_id'] = $user_id;
        $response = $this->post('/admin/category/edit-proc', $updData);
        $db = new Category();
        $id = $db->getCategoryLastID();
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/category/edit?id=' . $id);
        $response
            ->assertSee('<dd><input name="category_name" type="text" value="' . $str50En . '"></dd>', false)
            ->assertSee('<dd><input name="disp_name" type="text" value="' . $dispName . '"></dd>', false)
            ->assertSee('<option value="' . $sort . '"  selected >' . $sort . '</option>', false);

        // カテゴリー名 51文字
        $updData = [];
        $updData['disp_name'] = $dispName;
        $updData['category_name'] = $str50En . 'N';
        $updData['sort_no'] = $sort;
        $updData['user_id'] = $user_id;
        $response = $this->post('/admin/category/edit-proc', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['category_name' => '50字以内で入力してください。']);

        // カテゴリー名 半角英数字、アンダーバー、ハイフン（OK）
        $updData = [];
        $updData['disp_name'] = $dispName;
        $updData['category_name'] = 'abc-_12';
        $updData['sort_no'] = $sort;
        $updData['user_id'] = $user_id;
        $response = $this->post('/admin/category/edit-proc', $updData);
        $db = new Category();
        $id = $db->getCategoryLastID();
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/category/edit?id=' . $id);
        $response
            ->assertSee('<dd><input name="category_name" type="text" value="abc-_12"></dd>', false)
            ->assertSee('<dd><input name="disp_name" type="text" value="' . $dispName . '"></dd>', false)
            ->assertSee('<option value="' . $sort . '"  selected >' . $sort . '</option>', false);

        // カテゴリー名 半角英数字（OK）
        $updData = [];
        $updData['disp_name'] = $dispName;
        $updData['category_name'] = 'abc52';
        $updData['sort_no'] = $sort;
        $updData['user_id'] = $user_id;
        $response = $this->post('/admin/category/edit-proc', $updData);
        $db = new Category();
        $id = $db->getCategoryLastID();
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/category/edit?id=' . $id);
        $response
            ->assertSee('<dd><input name="category_name" type="text" value="abc52"></dd>', false)
            ->assertSee('<dd><input name="disp_name" type="text" value="' . $dispName . '"></dd>', false)
            ->assertSee('<option value="' . $sort . '"  selected >' . $sort . '</option>', false);

        // カテゴリー名 半角英字（OK）
        $updData = [];
        $updData['disp_name'] = $dispName;
        $updData['category_name'] = 'xyz';
        $updData['sort_no'] = $sort;
        $updData['user_id'] = $user_id;
        $response = $this->post('/admin/category/edit-proc', $updData);
        $db = new Category();
        $id = $db->getCategoryLastID();
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/category/edit?id=' . $id);
        $response
            ->assertSee('<dd><input name="category_name" type="text" value="xyz"></dd>', false)
            ->assertSee('<dd><input name="disp_name" type="text" value="' . $dispName . '"></dd>', false)
            ->assertSee('<option value="' . $sort . '"  selected >' . $sort . '</option>', false);

        // カテゴリー名 半角数字（OK）
        $updData = [];
        $updData['disp_name'] = $dispName;
        $updData['category_name'] = '098';
        $updData['sort_no'] = $sort;
        $updData['user_id'] = $user_id;
        $response = $this->post('/admin/category/edit-proc', $updData);
        $db = new Category();
        $id = $db->getCategoryLastID();
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/category/edit?id=' . $id);
        $response
            ->assertSee('<dd><input name="category_name" type="text" value="098"></dd>', false)
            ->assertSee('<dd><input name="disp_name" type="text" value="' . $dispName . '"></dd>', false)
            ->assertSee('<option value="' . $sort . '"  selected >' . $sort . '</option>', false);

        // カテゴリー名重複
        $categoryName = 'FeatureTestCategory';
        $updData = [];
        $updData['disp_name'] = $dispName;
        $updData['category_name'] = $categoryName;
        $updData['sort_no'] = $sort;
        $updData['user_id'] = $user_id;
        $response = $this->post('/admin/category/edit-proc', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['category_name' => '既に存在します。別の情報を入力してください。']);

        $categoryName = $categoryName . 'PQR';
        // ソート順空（OK）
        $updData = [];
        $updData['disp_name'] = $dispName;
        $updData['category_name'] = $categoryName;
        $updData['sort_no'] = '';
        $updData['user_id'] = $user_id;
        $response = $this->post('/admin/category/edit-proc', $updData);
        $db = new Category();
        $id = $db->getCategoryLastID();
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/category/edit?id=' . $id);
        $response
            ->assertSee('<dd><input name="category_name" type="text" value="' . $categoryName . '"></dd>', false)
            ->assertSee('<dd><input name="disp_name" type="text" value="' . $dispName . '"></dd>', false)
            ->assertDontSeeText('selected');

        $categoryName = $categoryName . 'STU';
        // ソート順指定外
        $updData = [];
        $updData['disp_name'] = $dispName;
        $updData['category_name'] = $categoryName;
        $updData['sort_no'] = 'aaa';
        $updData['user_id'] = $user_id;
        $response = $this->post('/admin/category/edit-proc', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['sort_no' => '半角数字で入力してください。']);

        // ソート順 存在しない数値
        $updData = [];
        $updData['disp_name'] = $dispName;
        $updData['category_name'] = $categoryName;
        $updData['sort_no'] = 999;
        $updData['user_id'] = $user_id;
        $response = $this->post('/admin/category/edit-proc', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['sort_no' => '不正な値が入力されています。']);
    }

    /**
     * カテゴリー投稿画面 編集
     * 通常（成功）
     */
    public function test_editCategory()
    {
        // 管理者が投稿する
        $this->dummyAdminLogin();

        $id = 5;
        $dispName = 'カテゴリー更新テスト';
        $categoryName = 'FeatureTestCategoryEdit';
        $sort = 10;
        $user_id = Auth::user()->id;

        $updData = [];
        $updData['id'] = $id;
        $updData['disp_name'] = $dispName;
        $updData['category_name'] = $categoryName;
        $updData['sort_no'] = $sort;
        $updData['user_id'] = $user_id;
        $response = $this->post('/admin/category/edit-proc?id=' . $id, $updData);
        $response->assertStatus(302);

        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/category/edit?id=' . $id);
        $response
            ->assertSee('<dd><input name="category_name" type="text" value="' . $categoryName . '"></dd>', false)
            ->assertSee('<dd><input name="disp_name" type="text" value="' . $dispName . '"></dd>', false)
            ->assertSee('<option value="' . $sort . '"  selected >' . $sort . '</option>', false);
    }
    /**
     * カテゴリー編集画面 編集エラー
     */
    public function test_editCategoryErr()
    {
        $this->dummyAdminLogin();

        $dispName = 'カテゴリー登録テスト';
        $categoryName = 'FeatureTestCategory';
        $sort = 10;
        $user_id = Auth::user()->id;

        $updData = [];
        $updData['disp_name'] = $dispName;
        $updData['category_name'] = $categoryName;
        $updData['sort_no'] = $sort;
        $updData['user_id'] = $user_id;

        // 存在しないID
        $response = $this->post('/admin/category/edit-proc?id=999', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');

        // ID指定外
        $response = $this->post('/admin/category/edit-proc?id=aaa', $updData);
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
