<?php
/**
 * 記事一覧、登録機能の試験
 */
namespace Tests\Feature;

use App\Models\Users;
use App\Models\Article;
use App\Models\SaveFile;
use App\Models\RelatedCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    /**
     * 記事関係のページに認証が必要なことを確認する
     */
    public function test_denyAccess()
    {
        $response = $this->get('/admin/article');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');

        $response = $this->get('/admin/article/private?id=1');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');

        $response = $this->get('/admin/article/publish?id=1');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');

        $response = $this->get('/admin/article/delete-confirm?id=1');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');

        $response = $this->post('/admin/article/delete-proc?id=1');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');

        $response = $this->get('/admin/article/edit');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');

        $response = $this->get('/admin/article/edit?id=1');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');

        $response = $this->post('/admin/article/upload-image');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');

        $response = $this->post('/admin/article/preview');
        $response
            ->assertStatus(302)
            ->assertRedirect('/login');
    }

    /**
     * 記事一覧、編集、削除アクセス
     * 管理者ログイン
     */
    public function test_adminAccess()
    {
        // 本番用としてチェックする（CSRFを有効にする）
        $this->app['env'] = 'production';
        $this->dummyAdminLogin();

        $response = $this->get('/admin/article');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.article.index');

        $response = $this->get('/admin/article/edit');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.article.edit');

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/article/edit-proc');
        $response->assertStatus(419);

        $response = $this->get('/admin/article/edit?id=1');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.article.edit');

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/article/edit-proc?id=1');
        $response->assertStatus(419);

        $response = $this->get('/admin/article/delete-confirm?id=1');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.article.delete-confirm');

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/article/delete-proc?id=1');
        $response->assertStatus(419);

        $response = $this->get('/admin/article/private?id=1');
        $response
            ->assertStatus(302)
            ->assertSessionHas('flashmessage', '「テスト投稿タイトル01」を非公開にしました。');

        $response = $this->get('/admin/article/publish?id=1');
        $response
            ->assertStatus(302)
            ->assertSessionHas('flashmessage', '「テスト投稿タイトル01」を公開中にしました。');

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/article/upload-image');
        $response->assertStatus(419);

        // プレビュー画面 CSRFが動作する
        $response = $this->post('/admin/article/preview');
        $response->assertStatus(419);
    }

    /**
     * 記事一覧、編集、削除アクセス
     * 一般ユーザー
     */
    public function test_userAccess()
    {
        // 本番用としてチェックする（CSRFを有効にする）
        $this->app['env'] = 'production';
        $this->dummyUserLogin();

        $response = $this->get('/admin/article');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.article.index');

        $response = $this->get('/admin/article/edit');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.article.edit');

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/article/edit-proc');
        $response->assertStatus(419);

        // 自分で作成した記事以外は編集できない
        $response = $this->get('/admin/article/edit?id=1');
        $response->assertStatus(302);

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/article/edit-proc?id=1');
        $response->assertStatus(419);

        // 削除不可
        $response = $this->get('/admin/article/delete-confirm?id=1');
        $response->assertStatus(302);

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/article/delete-proc?id=1');
        $response->assertStatus(419);

        // 自分の所有していないページは非公開にできない
        $response = $this->get('/admin/article/private?id=1');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');

        // 自分の所有していないページは公開にできない
        $response = $this->get('/admin/article/publish?id=1');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');

        // 実行ページは通常CSRFが動作するのでCSRFエラーとなるのが正常
        $response = $this->post('/admin/article/upload-image');
        $response->assertStatus(419);

        // プレビュー画面 CSRFが動作する
        $response = $this->post('/admin/article/preview');
        $response->assertStatus(419);
    }

    /**
     * 記事一覧の検索
     * 通常（成功）
     */
    public function test_searchArticle()
    {
        // 管理者
        $this->dummyAdminLogin();
        $response = $this->get('/admin/article?status=');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.article.index')
            ->assertSee('<li><div class="post_title">', false)
            ->assertSee('<div class="post_status">', false)
            ->assertSee('<div class="post_date">', false)
            ->assertSee('<div class="post_date">作成者：', false)
            ->assertSee('<li class="article-btn"><ul>', false)
            ->assertDontSee('<div class="result-msg">表示する情報がありません。', false);

        $response = $this->get('/admin/article?status=&page=10');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.article.index')
            ->assertSee('<li><div class="post_title">', false)
            ->assertSee('<div class="post_status">', false)
            ->assertSee('<div class="post_date">', false)
            ->assertSee('<div class="post_date">作成者：', false)
            ->assertSee('<li class="article-btn"><ul>', false)
            ->assertDontSee('<div class="result-msg">表示する情報がありません。', false);

        // 存在しないページ
        $response = $this->get('/admin/article?status=&page=10000');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.article.index')
            ->assertSee('<div class="result-msg">表示する情報がありません。新しい記事を書いてみませんか？</div>', false)
            ->assertDontSee('<li><div class="post_title">', false)
            ->assertDontSee('<div class="post_status">', false)
            ->assertDontSee('<div class="post_date">', false)
            ->assertDontSee('<div class="post_date">作成者：', false)
            ->assertDontSee('<li class="article-btn"><ul>', false);

        // 公開中検索
        $response = $this->get('/admin/article?status=1');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.article.index')
            ->assertSee('<div class="post_status"><span class=" publish-btn  disp-status">公開中</span></div>', false)
            ->assertDontSee('<div class="result-msg">表示する情報がありません。', false)
            ->assertDontSee('<div class="post_status"><span class=" private-btn  disp-status">非公開</span></div>', false)
            ->assertDontSee('<div class="post_status"><span class=" private-btn  disp-status">下書き</span></div>', false);

        $response = $this->get('/admin/article?status=1&page=2');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.article.index')
            ->assertSee('<div class="post_status"><span class=" publish-btn  disp-status">公開中</span></div>', false)
            ->assertDontSee('<div class="result-msg">表示する情報がありません。', false)
            ->assertDontSee('<div class="post_status"><span class=" private-btn  disp-status">非公開</span></div>', false)
            ->assertDontSee('<div class="post_status"><span class=" private-btn  disp-status">下書き</span></div>', false);

        // 非公開検索
        $response = $this->get('/admin/article?status=2');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.article.index')
            ->assertSee('<div class="post_status"><span class=" private-btn  disp-status">非公開</span></div>', false)
            ->assertDontSee('<div class="result-msg">表示する情報がありません。', false)
            ->assertDontSee('<div class="post_status"><span class=" publish-btn  disp-status">公開中</span></div>', false)
            ->assertDontSee('<div class="post_status"><span class=" private-btn  disp-status">下書き</span></div>', false);

        $response = $this->get('/admin/article?status=2&page=2');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.article.index')
            ->assertSee('<div class="post_status"><span class=" private-btn  disp-status">非公開</span></div>', false)
            ->assertDontSee('<div class="result-msg">表示する情報がありません。', false)
            ->assertDontSee('<div class="post_status"><span class=" publish-btn  disp-status">公開中</span></div>', false)
            ->assertDontSee('<div class="post_status"><span class=" private-btn  disp-status">下書き</span></div>', false);

        // 下書き検索
        $response = $this->get('/admin/article?status=3');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.article.index')
            ->assertSee('<div class="post_status"><span class=" private-btn  disp-status">下書き</span></div>', false)
            ->assertDontSee('<div class="result-msg">表示する情報がありません。', false)
            ->assertDontSee('<div class="post_status"><span class=" publish-btn  disp-status">公開中</span></div>', false)
            ->assertDontSee('<div class="post_status"><span class=" private-btn  disp-status">非公開</span></div>', false);

        $response = $this->get('/admin/article?status=3&page=2');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.article.index')
            ->assertSee('<div class="post_status"><span class=" private-btn  disp-status">下書き</span></div>', false)
            ->assertDontSee('<div class="result-msg">表示する情報がありません。', false)
            ->assertDontSee('<div class="post_status"><span class=" publish-btn  disp-status">公開中</span></div>', false)
            ->assertDontSee('<div class="post_status"><span class=" private-btn  disp-status">非公開</span></div>', false);

        // ユーザー
        $this->dummyUserLogin();
        $response = $this->get('/admin/article?status=');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.article.index')
            ->assertSee('<div class="result-msg">表示する情報がありません。新しい記事を書いてみませんか？</div>', false)
            ->assertDontSee('<li><div class="post_title">', false)
            ->assertDontSee('<div class="post_status">', false)
            ->assertDontSee('<div class="post_date">', false)
            ->assertDontSee('<div class="post_date">作成者：', false)
            ->assertDontSee('<li class="article-btn"><ul>', false);

        // 存在しないページ
        $response = $this->get('/admin/article?status=&page=10000');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.article.index')
            ->assertSee('<div class="result-msg">表示する情報がありません。新しい記事を書いてみませんか？</div>', false)
            ->assertDontSee('<li><div class="post_title">', false)
            ->assertDontSee('<div class="post_status">', false)
            ->assertDontSee('<div class="post_date">', false)
            ->assertDontSee('<div class="post_date">作成者：', false)
            ->assertDontSee('<li class="article-btn"><ul>', false);

        // 公開中検索
        $response = $this->get('/admin/article?status=1');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.article.index')
            ->assertSee('<div class="result-msg">表示する情報がありません。別の条件で検索してください。</div>', false)
            ->assertDontSee('<li><div class="post_title">', false)
            ->assertDontSee('<div class="post_status">', false)
            ->assertDontSee('<div class="post_date">', false)
            ->assertDontSee('<div class="post_date">作成者：', false)
            ->assertDontSee('<li class="article-btn"><ul>', false);

        // 非公開検索
        $response = $this->get('/admin/article?status=2');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.article.index')
            ->assertSee('<div class="result-msg">表示する情報がありません。別の条件で検索してください。</div>', false)
            ->assertDontSee('<li><div class="post_title">', false)
            ->assertDontSee('<div class="post_status">', false)
            ->assertDontSee('<div class="post_date">', false)
            ->assertDontSee('<div class="post_date">作成者：', false)
            ->assertDontSee('<li class="article-btn"><ul>', false);

        // 下書き検索
        $response = $this->get('/admin/article?status=3');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.article.index')
            ->assertSee('<div class="result-msg">表示する情報がありません。別の条件で検索してください。</div>', false)
            ->assertDontSee('<li><div class="post_title">', false)
            ->assertDontSee('<div class="post_status">', false)
            ->assertDontSee('<div class="post_date">', false)
            ->assertDontSee('<div class="post_date">作成者：', false)
            ->assertDontSee('<li class="article-btn"><ul>', false);
    }
    /**
     * 記事一覧の検索（異常系）
     */
    public function test_searchArticleErr()
    {
        // 存在しないステータス
        $response = $this->get('/admin/article?status=aaa');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');
    }

    /**
     * 記事一覧からの非公開
     * 通常（成功）
     */
    public function test_privateArticle()
    {
        $this->dummyAdminLogin();
        $response = $this->get('/admin/article/private?id=1');
        $response
            ->assertStatus(302)
            ->assertSessionHas('flashmessage', '「テスト投稿タイトル01」を非公開にしました。');
        // 非公開ステータスになったことを確認
        $response = $this->get('/admin/article/edit?id=1');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.article.edit')
            ->assertSee('<option value="2"  selected >非公開</option>', false);
    }
    /**
     * 記事一覧からの非公開（異常系）
     */
    public function test_privateArticleErr()
    {
        $this->dummyAdminLogin();
        // ID空（記事一覧リダイレクト）
        $response = $this->get('/admin/article/private?id=');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');

        // 存在しないID
        $response = $this->get('/admin/article/private?id=9999999');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');

        // 数値以外
        $response = $this->get('/admin/article/private?id=aaa');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');
    }
    /**
     * 記事一覧からの公開
     * 通常（成功）
     */
    public function test_publishArticle()
    {
        $this->dummyAdminLogin();
        $response = $this->get('/admin/article/publish?id=2');
        $response
            ->assertStatus(302)
            ->assertSessionHas('flashmessage', '「テスト投稿タイトル02」を公開中にしました。');
        // 公開ステータスになったことを確認
        $response = $this->get('/admin/article/edit?id=2');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.article.edit')
            ->assertSee('<option value="1"  selected >公開中</option>', false);
    }
    /**
     * 記事一覧からの公開（異常系）
     */
    public function test_publishArticleErr()
    {
        $this->dummyAdminLogin();
        // ID空（記事一覧リダイレクト）
        $response = $this->get('/admin/article/publish?id=');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');

        // 存在しないID
        $response = $this->get('/admin/article/publish?id=9999999');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');

        // 数値以外
        $response = $this->get('/admin/article/publish?id=aaa');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');
    }
    /**
     * 記事一覧からの削除確認
     * 通常（成功）
     */
    public function test_deleteConfirmArticle()
    {
        $this->dummyAdminLogin();
        $response = $this->get('/admin/article/delete-confirm?id=1');
        $response
            ->assertStatus(200)
            ->assertViewIs('admin.article.delete-confirm')
            ->assertSee('<dd class="article_parts article_title">テスト投稿タイトル01</dd>', false)
            ->assertSee('<dd class="article_parts">これはテスト投稿です<br></dd>', false)
            ->assertSee('<dd class="article_parts">公開中</dd>', false)
            ->assertSee('<dd class="article_parts article_select">管理者のみ</dd>', false)
            ->assertSeeText('（公開者：管理者）')
            ->assertSeeText('（最終更新者：管理者）');
    }
    /**
     * 記事一覧からの削除確認（異常系）
     */
    public function test_deleteConfirmArticleErr()
    {
        $this->dummyAdminLogin();
        // ID空（記事一覧リダイレクト）
        $response = $this->get('/admin/article/delete-confirm?id=');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');

        // 存在しないID
        $response = $this->get('/admin/article/delete-confirm?id=9999999');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');

        // 数値以外
        $response = $this->get('/admin/article/delete-confirm?id=aaa');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');
    }
    /**
     * 記事一覧からの削除
     * 通常（成功）
     */
    public function test_deleteProcArticle()
    {
        $this->dummyAdminLogin();
        $response = $this->post('/admin/article/delete-proc?id=1');
        $response
            ->assertStatus(302)
            ->assertSessionHas('flashmessage', '記事を削除しました。');
    }
    /**
     * 記事一覧からの削除（異常系）
     */
    public function test_deleteProcArticleErr()
    {
        $this->dummyAdminLogin();
        // ID空（記事一覧リダイレクト）
        $response = $this->post('/admin/article/delete-proc?id=');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');

        // 存在しないID
        $response = $this->post('/admin/article/delete-proc?id=9999999');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');

        // 数値以外
        $response = $this->post('/admin/article/delete-proc?id=aaa');
        $response
            ->assertStatus(302)
            ->assertSessionMissing('flashmessage');
    }
    /**
     * 記事投稿画面 新規
     * 通常（成功）
     */
    public function test_addArticle()
    {
        // 保存したファイルの削除
        Storage::disk('public')->deleteDirectory('uploads');

        // 管理者が投稿する
        $this->dummyAdminLogin();
        $uploadedFile = UploadedFile::fake()->image('test.jpg');

        $title = 'FeatureTestTitle';
        $contents = '<p>FeatureTestContents</p>';
        $status = config('umekoset.status_publish');
        $path = 'feature_test';
        $article_auth = config('umekoset.article_auth_admin');
        $seo = 'FeatureTestSeo';
        $category = mt_rand(1,20);
        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');
        $day = Carbon::now()->format('d');
        $hour = Carbon::now()->format('h');
        $min = Carbon::now()->format('i');
        $icathPath = asset('storage/uploads/image/' . $year . '/' . $month . '/small/' . $uploadedFile->name);

        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['category'][] = $category;
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $updData['icatch'] = $uploadedFile;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response->assertStatus(302);

        $db = new Article();
        $id = $db->getArticleLastID();
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/article/edit?id=' . $id);
        $response
            ->assertSee('<img src=" ' . $icathPath . ' "', false)
            ->assertSee($contents, false)
            ->assertSee('<option value="' . $year. '"  selected >' . $year . '</option>', false)
            ->assertSee('<option value="' . $month . '"  selected >' . (int)$month . '</option>', false)
            ->assertSee('<option value="' . $day . '"  selected >' . (int)$day . '</option>', false)
            ->assertSee('<option value="' . $hour . '"  selected >' . $hour . '</option>', false)
            ->assertSee('<option value="' . $min . '"  selected >' . $min . '</option>', false)
            ->assertSee($title, false)
            ->assertSee($path, false)
            ->assertSeeText(config('umekoset.status')[$status])
            ->assertSeeText(config('umekoset.article_auth')[$article_auth])
            ->assertSeeText($seo);

        // 保存したファイルの削除
        Storage::disk('public')->deleteDirectory('uploads');

        // ユーザーが投稿する
        $this->dummyUserLogin();
        $uploadedFile = UploadedFile::fake()->image('test.jpg');

        $title = 'FeatureTestTitle';
        $contents = '<p>FeatureTestContents</p>';
        $status = config('umekoset.status_publish');
        $path = 'feature_test_user';
        $article_auth = config('umekoset.article_auth_creator');
        $seo = 'FeatureTestSeo';
        $category = mt_rand(1,20);
        $category02 = mt_rand(1,20); // カテゴリー複数登録確認
        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');
        $day = Carbon::now()->format('d');
        $hour = Carbon::now()->format('h');
        $min = Carbon::now()->format('i');
        $icathPath = asset('storage/uploads/image/' . $year . '/' . $month . '/small/' . $uploadedFile->name);

        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['category'][] = $category;
        $updData['category'][] = $category02; // カテゴリー複数登録確認
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $updData['icatch'] = $uploadedFile;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response->assertStatus(302);

        $db = new Article();
        $id = $db->getArticleLastID();
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/article/edit?id=' . $id);
        $response
            ->assertSee('<img src=" ' . $icathPath . ' "', false)
            ->assertSee($contents, false)
            ->assertSee('<option value="' . $year. '"  selected >' . $year . '</option>', false)
            ->assertSee('<option value="' . $month . '"  selected >' . (int)$month . '</option>', false)
            ->assertSee('<option value="' . $day . '"  selected >' . (int)$day . '</option>', false)
            ->assertSee('<option value="' . $hour . '"  selected >' . $hour . '</option>', false)
            ->assertSee('<option value="' . $min . '"  selected >' . $min . '</option>', false)
            ->assertSee($title, false)
            ->assertSee($path, false)
            ->assertSeeText(config('umekoset.status')[$status])
            ->assertSeeText($seo);

        // 保存したファイルの削除
        Storage::disk('public')->deleteDirectory('uploads');
    }
    /**
     * 記事投稿処理 新規投稿エラー
     */
    public function test_addArticleErr()
    {
        // 保存したファイルの削除
        Storage::disk('public')->deleteDirectory('uploads');

        $this->dummyAdminLogin();
        $uploadedFile = UploadedFile::fake()->image('test.jpg');

        $title = 'FeatureTestTitle';
        $contents = '<p>FeatureTestContents</p>';
        $status = config('umekoset.status_publish');
        $path = 'feature_test';
        $article_auth = config('umekoset.article_auth_admin');
        $seo = 'FeatureTestSeo';
        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');
        $day = Carbon::now()->format('d');
        $hour = Carbon::now()->format('h');
        $min = Carbon::now()->format('i');
        $category = mt_rand(1,20);
        $icathPath = asset('storage/uploads/image/' . $year . '/' . $month . '/small/' . $uploadedFile->name);
        $str255 = '';
        for($i=1; $i<256; $i++) {
            $str255 .= 'あ';
        }
        $str255En = '';
        for($i=1; $i<256; $i++) {
            $str255En .= 'a';
        }

        // 投稿タイトル空
        $updData = [];
        $updData['post_title'] = '';
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = 'aaa';
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $updData['icatch'] = $uploadedFile;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['post_title' => '必須項目です。']);

        // 投稿タイトル255文字（OK）
        $updData = [];
        $updData['post_title'] = $str255;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['category'][] = $category;
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response->assertStatus(302);

        $db = new Article();
        $id = $db->getArticleLastID();
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/article/edit?id=' . $id);
        $response
            ->assertSee($contents, false)
            ->assertSee('<option value="' . $year. '"  selected >' . $year . '</option>', false)
            ->assertSee('<option value="' . $month . '"  selected >' . (int)$month . '</option>', false)
            ->assertSee('<option value="' . $day . '"  selected >' . (int)$day . '</option>', false)
            ->assertSee('<option value="' . $hour . '"  selected >' . $hour . '</option>', false)
            ->assertSee('<option value="' . $min . '"  selected >' . $min . '</option>', false)
            ->assertSee($str255, false)
            ->assertSee($path, false)
            ->assertSeeText(config('umekoset.status')[$status])
            ->assertSeeText(config('umekoset.article_auth')[$article_auth])
            ->assertSeeText($seo);

        // 投稿タイトル255文字以上
        $updData = [];
        $updData['post_title'] = $str255 . 'あ';
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['category'][] = $category;
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $updData['icatch'] = $uploadedFile;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['post_title' => '255字以内で入力してください。']);

        // 本文空
        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = '';
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['category'][] = $category;
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $updData['icatch'] = $uploadedFile;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['trumbowyg-editor' => '必須項目です。']);

        // ステータス空
        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = '';
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['category'][] = $category;
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $updData['icatch'] = $uploadedFile;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['status' => '不正な値が入力されています。']);

        // ステータス指定外
        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = 'aaa';
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['category'][] = $category;
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $updData['icatch'] = $uploadedFile;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['status' => '不正な値が入力されています。']);

        // パス255文字（OK）
        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $str255En;
        $updData['article_auth'] = $article_auth;
        $updData['category'][] = $category;
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response->assertStatus(302);

        $db = new Article();
        $id = $db->getArticleLastID();
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/article/edit?id=' . $id);
        $response
            ->assertSee($contents, false)
            ->assertSee('<option value="' . $year. '"  selected >' . $year . '</option>', false)
            ->assertSee('<option value="' . $month . '"  selected >' . (int)$month . '</option>', false)
            ->assertSee('<option value="' . $day . '"  selected >' . (int)$day . '</option>', false)
            ->assertSee('<option value="' . $hour . '"  selected >' . $hour . '</option>', false)
            ->assertSee('<option value="' . $min . '"  selected >' . $min . '</option>', false)
            ->assertSee($title, false)
            ->assertSee($str255En, false)
            ->assertSeeText(config('umekoset.status')[$status])
            ->assertSeeText(config('umekoset.article_auth')[$article_auth])
            ->assertSeeText($seo);

        // パス256文字
        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $str255En . 'Z';
        $updData['article_auth'] = $article_auth;
        $updData['category'][] = $category;
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['path' => '255字以内で入力してください。']);

        // パス半角英数字
        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = 'aaa123';
        $updData['article_auth'] = $article_auth;
        $updData['category'][] = $category;
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response->assertStatus(302);

        $db = new Article();
        $id = $db->getArticleLastID();
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/article/edit?id=' . $id);
        $response
            ->assertSee($contents, false)
            ->assertSee('<option value="' . $year. '"  selected >' . $year . '</option>', false)
            ->assertSee('<option value="' . $month . '"  selected >' . (int)$month . '</option>', false)
            ->assertSee('<option value="' . $day . '"  selected >' . (int)$day . '</option>', false)
            ->assertSee('<option value="' . $hour . '"  selected >' . $hour . '</option>', false)
            ->assertSee('<option value="' . $min . '"  selected >' . $min . '</option>', false)
            ->assertSee($title, false)
            ->assertSee('aaa123', false)
            ->assertSeeText(config('umekoset.status')[$status])
            ->assertSeeText(config('umekoset.article_auth')[$article_auth])
            ->assertSeeText($seo);

        // パス半角英数字とアンダーバー、ハイフン（OK）
        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = 'aaa-1_23';
        $updData['article_auth'] = $article_auth;
        $updData['category'][] = $category;
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response->assertStatus(302);

        $db = new Article();
        $id = $db->getArticleLastID();
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/article/edit?id=' . $id);
        $response
            ->assertSee($contents, false)
            ->assertSee('<option value="' . $year. '"  selected >' . $year . '</option>', false)
            ->assertSee('<option value="' . $month . '"  selected >' . (int)$month . '</option>', false)
            ->assertSee('<option value="' . $day . '"  selected >' . (int)$day . '</option>', false)
            ->assertSee('<option value="' . $hour . '"  selected >' . $hour . '</option>', false)
            ->assertSee('<option value="' . $min . '"  selected >' . $min . '</option>', false)
            ->assertSee($title, false)
            ->assertSee('aaa-1_23', false)
            ->assertSeeText(config('umekoset.status')[$status])
            ->assertSeeText(config('umekoset.article_auth')[$article_auth])
            ->assertSeeText($seo);

        // パス半角英数字、アンダーバー、ハイフン以外
        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = 'aあ/';
        $updData['article_auth'] = $article_auth;
        $updData['seo_description'] = $seo;
        $updData['category'][] = $category;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['path' => '半角英数字と記号（ハイフン、アンダーバー）で入力してください。']);

        // 編集権限空
        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = '';
        $updData['seo_description'] = $seo;
        $updData['category'][] = $category;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $updData['icatch'] = $uploadedFile;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['article_auth' => '不正な値が入力されています。']);

        // 編集権限指定外
        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = 'aaa';
        $updData['seo_description'] = $seo;
        $updData['category'][] = $category;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $updData['icatch'] = $uploadedFile;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['article_auth' => '不正な値が入力されています。']);

        // seo description 空（OK）
        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['seo_description'] = '';
        $updData['category'][] = $category;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response->assertStatus(302);

        $db = new Article();
        $id = $db->getArticleLastID();
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/article/edit?id=' . $id);
        $response
            ->assertSee($contents, false)
            ->assertSee('<option value="' . $year. '"  selected >' . $year . '</option>', false)
            ->assertSee('<option value="' . $month . '"  selected >' . (int)$month . '</option>', false)
            ->assertSee('<option value="' . $day . '"  selected >' . (int)$day . '</option>', false)
            ->assertSee('<option value="' . $hour . '"  selected >' . $hour . '</option>', false)
            ->assertSee('<option value="' . $min . '"  selected >' . $min . '</option>', false)
            ->assertSee($title, false)
            ->assertSee($path, false)
            ->assertSeeText(config('umekoset.status')[$status])
            ->assertSeeText(config('umekoset.article_auth')[$article_auth]);

        // seo description 255字（OK）
        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['seo_description'] = $str255;
        $updData['category'][] = $category;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response->assertStatus(302);

        $db = new Article();
        $id = $db->getArticleLastID();
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/article/edit?id=' . $id);
        $response
            ->assertSee($contents, false)
            ->assertSee('<option value="' . $year. '"  selected >' . $year . '</option>', false)
            ->assertSee('<option value="' . $month . '"  selected >' . (int)$month . '</option>', false)
            ->assertSee('<option value="' . $day . '"  selected >' . (int)$day . '</option>', false)
            ->assertSee('<option value="' . $hour . '"  selected >' . $hour . '</option>', false)
            ->assertSee('<option value="' . $min . '"  selected >' . $min . '</option>', false)
            ->assertSee($title, false)
            ->assertSee($path, false)
            ->assertSeeText(config('umekoset.status')[$status])
            ->assertSeeText(config('umekoset.article_auth')[$article_auth])
            ->assertSeeText($str255);

        // seo description 256文字
        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['seo_description'] = $str255 . '&';
        $updData['category'][] = $category;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['seo_description' => '255字以内で入力してください。']);

        // 公開日時 年が異なる
        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['seo_description'] = $seo;
        $updData['category'][] = $category;
        $updData['open_year'] = 'aaaa';
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['publish_at' => '日付の形式に誤りがあります。']);

        // 公開日時 月が異なる
        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['seo_description'] = $seo;
        $updData['category'][] = $category;
        $updData['open_year'] = $year;
        $updData['open_month'] = 13;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['publish_at' => '日付の形式に誤りがあります。']);

        // 公開日時 日が異なる
        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['seo_description'] = $seo;
        $updData['category'][] = $category;
        $updData['open_year'] = $year;
        $updData['open_month'] = 2;
        $updData['open_day'] = 30;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['publish_at' => '日付の形式に誤りがあります。']);

        // 公開日時 時が異なる
        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['seo_description'] = $seo;
        $updData['category'][] = $category;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = 24;
        $updData['open_min'] = $min;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['publish_at' => '日付の形式に誤りがあります。']);

        // 公開日時 分が異なる
        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['seo_description'] = $seo;
        $updData['category'][] = $category;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = 60;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['publish_at' => '日付の形式に誤りがあります。']);

        // アイキャッチ容量いっぱい（OK）
        $uploadedFile = UploadedFile::fake()->image('limit.jpg')->size(config('umekoset.max_filesize'));
        $icathPath = asset('storage/uploads/image/' . $year . '/' . $month . '/small/' . $uploadedFile->name);
        $path = 'feature_test02';

        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['category'][] = $category;
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $updData['icatch'] = $uploadedFile;
        $response = $this->post('/admin/article/edit-proc', $updData);

        $db = new Article();
        $id = $db->getArticleLastID();
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/article/edit?id=' . $id);
        $response
            ->assertSee('<img src=" ' . $icathPath . ' "', false)
            ->assertSee($contents, false)
            ->assertSee('<option value="' . $year. '"  selected >' . $year . '</option>', false)
            ->assertSee('<option value="' . $month . '"  selected >' . (int)$month . '</option>', false)
            ->assertSee('<option value="' . $day . '"  selected >' . (int)$day . '</option>', false)
            ->assertSee('<option value="' . $hour . '"  selected >' . $hour . '</option>', false)
            ->assertSee('<option value="' . $min . '"  selected >' . $min . '</option>', false)
            ->assertSee($title, false)
            ->assertSee($path, false)
            ->assertSeeText(config('umekoset.status')[$status])
            ->assertSeeText(config('umekoset.article_auth')[$article_auth])
            ->assertSeeText($seo);

        // アイキャッチ容量オーバー
        $maxSize = config('umekoset.max_filesize') + 1;
        $uploadedFile = UploadedFile::fake()->image('limit.jpg')->size($maxSize);
        $icathPath = asset('storage/uploads/image/' . $year . '/' . $month . '/small/' . $uploadedFile->name);
        $path = 'feature_test02';

        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['category'][] = $category;
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $updData['icatch'] = $uploadedFile;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['icatch' => 'ファイルは' . config('umekoset.max_filesize') . 'kbyte以内にしてください。']);

        // 拡張子png（OK）
        $uploadedFile = UploadedFile::fake()->image('test.png');
        $icathPath = asset('storage/uploads/image/' . $year . '/' . $month . '/small/' . $uploadedFile->name);
        $path = 'feature_test03';

        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['category'][] = $category;
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $updData['icatch'] = $uploadedFile;
        $response = $this->post('/admin/article/edit-proc', $updData);

        $db = new Article();
        $id = $db->getArticleLastID();
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/article/edit?id=' . $id);
        $response
            ->assertSee('<img src=" ' . $icathPath . ' "', false)
            ->assertSee($contents, false)
            ->assertSee('<option value="' . $year. '"  selected >' . $year . '</option>', false)
            ->assertSee('<option value="' . $month . '"  selected >' . (int)$month . '</option>', false)
            ->assertSee('<option value="' . $day . '"  selected >' . (int)$day . '</option>', false)
            ->assertSee('<option value="' . $hour . '"  selected >' . $hour . '</option>', false)
            ->assertSee('<option value="' . $min . '"  selected >' . $min . '</option>', false)
            ->assertSee($title, false)
            ->assertSee($path, false)
            ->assertSeeText(config('umekoset.status')[$status])
            ->assertSeeText(config('umekoset.article_auth')[$article_auth])
            ->assertSeeText($seo);

        // 拡張子gif（OK）
        $uploadedFile = UploadedFile::fake()->image('test.gif');
        $icathPath = asset('storage/uploads/image/' . $year . '/' . $month . '/small/' . $uploadedFile->name);
        $path = 'feature_test04';

        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['category'][] = $category;
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $updData['icatch'] = $uploadedFile;
        $response = $this->post('/admin/article/edit-proc', $updData);

        $db = new Article();
        $id = $db->getArticleLastID();
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/article/edit?id=' . $id);
        $response
            ->assertSee('<img src=" ' . $icathPath . ' "', false)
            ->assertSee($contents, false)
            ->assertSee('<option value="' . $year. '"  selected >' . $year . '</option>', false)
            ->assertSee('<option value="' . $month . '"  selected >' . (int)$month . '</option>', false)
            ->assertSee('<option value="' . $day . '"  selected >' . (int)$day . '</option>', false)
            ->assertSee('<option value="' . $hour . '"  selected >' . $hour . '</option>', false)
            ->assertSee('<option value="' . $min . '"  selected >' . $min . '</option>', false)
            ->assertSee($title, false)
            ->assertSee($path, false)
            ->assertSeeText(config('umekoset.status')[$status])
            ->assertSeeText(config('umekoset.article_auth')[$article_auth])
            ->assertSeeText($seo);

        // 拡張子指定外
        $uploadedFile = UploadedFile::fake()->image('noex.pdf');
        $icathPath = asset('storage/uploads/image/' . $year . '/' . $month . '/small/' . $uploadedFile->name);
        $path = 'feature_test02';

        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['category'][] = $category;
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $updData['icatch'] = $uploadedFile;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['filetype' => '拡張子は' . implode(' 、 ', config('umekoset.image_ex')) . ' のいずれかのファイルを指定してください。']);

        // カテゴリー空（OK）
        $path = 'feature_test04';
        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $response = $this->post('/admin/article/edit-proc', $updData);

        $db = new Article();
        $id = $db->getArticleLastID();
        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/article/edit?id=' . $id);
        $response
            ->assertSee($contents, false)
            ->assertSee('<option value="' . $year. '"  selected >' . $year . '</option>', false)
            ->assertSee('<option value="' . $month . '"  selected >' . (int)$month . '</option>', false)
            ->assertSee('<option value="' . $day . '"  selected >' . (int)$day . '</option>', false)
            ->assertSee('<option value="' . $hour . '"  selected >' . $hour . '</option>', false)
            ->assertSee('<option value="' . $min . '"  selected >' . $min . '</option>', false)
            ->assertSee($title, false)
            ->assertSee($path, false)
            ->assertSeeText(config('umekoset.status')[$status])
            ->assertSeeText(config('umekoset.article_auth')[$article_auth])
            ->assertSeeText($seo);

        // 存在しないカテゴリー
        $path = 'feature_test04';
        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['category'][] = 999;
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $response = $this->post('/admin/article/edit-proc', $updData);
        $response
            ->assertStatus(302)
            ->assertSessionHasErrors(['category' => '不正な値が入力されています。']);

        Storage::disk('public')->deleteDirectory('uploads');
    }

    /**
     * 記事投稿画面 編集
     * 通常（成功）
     */
    public function test_editArticle()
    {
        // 保存したファイルの削除
        Storage::disk('public')->deleteDirectory('uploads');

        $this->dummyAdminLogin();
        $uploadedFile = UploadedFile::fake()->image('test.jpg');

        $id = 1;
        $title = 'FeatureTestTitle';
        $contents = '<p>FeatureTestContents</p>';
        $status = config('umekoset.status_publish');
        $path = 'feature_test';
        $article_auth = config('umekoset.article_auth_admin');
        $seo = 'Fe2tureTestSeo';
        $category01 = 1; // 既存カテゴリ
        $category02 = 2; // 既存カテゴリ
        $category03 = 3; // カテゴリ追加
        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');
        $day = Carbon::now()->format('d');
        $hour = Carbon::now()->format('h');
        $min = Carbon::now()->format('i');
        $icathPath = asset('storage/uploads/image/' . $year . '/' . $month . '/small/' . $uploadedFile->name);

        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['category'][] = $category01;
        $updData['category'][] = $category02;
        $updData['category'][] = $category03;
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $updData['icatch'] = $uploadedFile;
        $response = $this->post('/admin/article/edit-proc?id=' . $id, $updData);

        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/article/edit?id=' . $id);
        $response
            ->assertSee('<img src=" ' . $icathPath . ' "', false)
            ->assertSee($contents, false)
            ->assertSee('<option value="' . $year. '"  selected >' . $year . '</option>', false)
            ->assertSee('<option value="' . $month . '"  selected >' . (int)$month . '</option>', false)
            ->assertSee('<option value="' . $day . '"  selected >' . (int)$day . '</option>', false)
            ->assertSee('<option value="' . $hour . '"  selected >' . $hour . '</option>', false)
            ->assertSee('<option value="' . $min . '"  selected >' . $min . '</option>', false)
            ->assertSee($title, false)
            ->assertSee($path, false)
            ->assertSee('<input type="checkbox" name="category[]" id="category_1" value="1"  checked >カテゴリ01', false)
            ->assertSee('<input type="checkbox" name="category[]" id="category_2" value="2"  checked >カテゴリ02', false)
            ->assertSee('<input type="checkbox" name="category[]" id="category_3" value="3"  checked >カテゴリ03', false)
            ->assertSeeText(config('umekoset.status')[$status])
            ->assertSeeText(config('umekoset.article_auth')[$article_auth])
            ->assertSeeText($seo);

        // 既存カテゴリの削除を確認する
        $id = 1;
        $title = 'FeatureTestTitle';
        $contents = '<p>FeatureTestContents</p>';
        $status = config('umekoset.status_publish');
        $path = 'feature_test';
        $article_auth = config('umekoset.article_auth_admin');
        $seo = 'Fe2tureTestSeo';
        $category01 = 1; // 既存カテゴリ
        $category03 = 3; // カテゴリ追加
        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');
        $day = Carbon::now()->format('d');
        $hour = Carbon::now()->format('h');
        $min = Carbon::now()->format('i');

        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['category'][] = $category01;
        $updData['category'][] = $category03; // 02を削除
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $response = $this->post('/admin/article/edit-proc?id=' . $id, $updData);

        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/article/edit?id=' . $id);
        $response
            ->assertSee('<img src=" ' . $icathPath . ' "', false)
            ->assertSee($contents, false)
            ->assertSee('<option value="' . $year. '"  selected >' . $year . '</option>', false)
            ->assertSee('<option value="' . $month . '"  selected >' . (int)$month . '</option>', false)
            ->assertSee('<option value="' . $day . '"  selected >' . (int)$day . '</option>', false)
            ->assertSee('<option value="' . $hour . '"  selected >' . $hour . '</option>', false)
            ->assertSee('<option value="' . $min . '"  selected >' . $min . '</option>', false)
            ->assertSee($title, false)
            ->assertSee($path, false)
            ->assertSee('<input type="checkbox" name="category[]" id="category_1" value="1"  checked >カテゴリ01', false)
            ->assertDontSee('<input type="checkbox" name="category[]" id="category_2" value="2"  checked >カテゴリ02', false)
            ->assertSee('<input type="checkbox" name="category[]" id="category_2" value="2" >カテゴリ02', false)
            ->assertSee('<input type="checkbox" name="category[]" id="category_3" value="3"  checked >カテゴリ03', false)
            ->assertSeeText(config('umekoset.status')[$status])
            ->assertSeeText(config('umekoset.article_auth')[$article_auth])
            ->assertSeeText($seo);

        // 既存カテゴリの変更なしの場合を確認
        $id = 1;
        $title = 'FeatureTestTitle';
        $contents = '<p>FeatureTestContents</p>';
        $status = config('umekoset.status_publish');
        $path = 'feature_test';
        $article_auth = config('umekoset.article_auth_admin');
        $seo = 'Fe2tureTestSeo';
        $category01 = 1; // 既存カテゴリ
        $category03 = 3; // 既存カテゴリ
        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');
        $day = Carbon::now()->format('d');
        $hour = Carbon::now()->format('h');
        $min = Carbon::now()->format('i');

        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['category'][] = $category01;
        $updData['category'][] = $category03;
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $response = $this->post('/admin/article/edit-proc?id=' . $id, $updData);

        // リダイレクト先の編集画面へ移動して登録データが存在するか確認
        $response = $this->get('/admin/article/edit?id=' . $id);
        $response
            ->assertSee('<img src=" ' . $icathPath . ' "', false)
            ->assertSee($contents, false)
            ->assertSee('<option value="' . $year. '"  selected >' . $year . '</option>', false)
            ->assertSee('<option value="' . $month . '"  selected >' . (int)$month . '</option>', false)
            ->assertSee('<option value="' . $day . '"  selected >' . (int)$day . '</option>', false)
            ->assertSee('<option value="' . $hour . '"  selected >' . $hour . '</option>', false)
            ->assertSee('<option value="' . $min . '"  selected >' . $min . '</option>', false)
            ->assertSee($title, false)
            ->assertSee($path, false)
            ->assertSee('<input type="checkbox" name="category[]" id="category_1" value="1"  checked >カテゴリ01', false)
            ->assertSee('<input type="checkbox" name="category[]" id="category_3" value="3"  checked >カテゴリ03', false)
            ->assertSeeText(config('umekoset.status')[$status])
            ->assertSeeText(config('umekoset.article_auth')[$article_auth])
            ->assertSeeText($seo);

        // 保存したファイルの削除
        Storage::disk('public')->deleteDirectory('uploads');
    }
    /**
     * 記事編集画面 編集エラー
     */
    public function test_editArticleErr()
    {
        $this->dummyAdminLogin();

        $title = 'FeatureTestTitle';
        $contents = '<p>FeatureTestContents</p>';
        $status = config('umekoset.status_publish');
        $path = 'feature_test';
        $article_auth = config('umekoset.article_auth_admin');
        $seo = 'FeatureTestSeo';
        $category = mt_rand(1,20);
        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');
        $day = Carbon::now()->format('d');
        $hour = Carbon::now()->format('h');
        $min = Carbon::now()->format('i');

        // 存在しないID
        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['category'][] = $category;
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;

        $response = $this->post('/admin/article/edit-proc?id=99999', $updData);
        $response
            ->assertStatus(302)
            ->assertRedirect('/admin/article');

        // ID指定外
        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['category'][] = $category;
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;

        $response = $this->post('/admin/article/edit-proc?id=aaa', $updData);
        $response
            ->assertStatus(302)
            ->assertDontSeeText('記事の投稿');
    }

     /**
      * 画像アップロード（uploadImage宛）
      * 通常（成功）拡張子jpg
      * データベースの登録確認（成功する時点で登録はされているので、それを逆引きできるか）
      */
    public function test_uploadImage()
    {
        // 保存したファイルの削除
        Storage::disk('public')->deleteDirectory('uploads');

        $this->dummyAdminLogin();

        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');

        // 拡張子jpg（OK）
        $uploadedFile = UploadedFile::fake()->image('test.jpg');
        $alt = 'testjpg';
        $icatchPath = asset('storage/uploads/image/' . $year . '/' . $month . '/middle/' . $uploadedFile->name);
        $updData = [];
        $updData['fileToUpload'] = $uploadedFile;
        $updData['alt'] = $alt;
        $response = $this->post('/admin/article/upload-image', $updData);
        // ファイルの存在チェック
        $db = new SaveFile();
        $id = $db->getFileLastID();
        $data = $db->getFile($id);
        $file = $data[0];
        $updFile = asset('storage/uploads/image/' . $file->year . '/' . $file->month . '/middle/' . $file->filename);
        $response
            ->assertStatus(200)
            ->assertJson(['success' => true, 'file' => $icatchPath, 'alt' => $alt])
            ->assertJson(['success' => true, 'file' => $updFile, 'alt' => $file->description]);

        // 拡張子png（OK）
        $uploadedFile = UploadedFile::fake()->image('test.png');
        $alt = 'testpng';
        $icatchPath = asset('storage/uploads/image/' . $year . '/' . $month . '/middle/' . $uploadedFile->name);
        $updData = [];
        $updData['fileToUpload'] = $uploadedFile;
        $updData['alt'] = $alt;
        $response = $this->post('/admin/article/upload-image', $updData);
        // ファイルの存在チェック
        $id = $db->getFileLastID();
        $data = $db->getFile($id);
        $file = $data[0];
        $updFile = asset('storage/uploads/image/' . $file->year . '/' . $file->month . '/middle/' . $file->filename);
        $response
            ->assertStatus(200)
            ->assertJson(['success' => true, 'file' => $icatchPath, 'alt' => $alt])
            ->assertJson(['success' => true, 'file' => $updFile, 'alt' => $file->description]);

        // 拡張子gif（OK）
        $uploadedFile = UploadedFile::fake()->image('test.gif');
        $alt = 'testgif';
        $icatchPath = asset('storage/uploads/image/' . $year . '/' . $month . '/middle/' . $uploadedFile->name);
        $updData = [];
        $updData['fileToUpload'] = $uploadedFile;
        $updData['alt'] = $alt;
        $response = $this->post('/admin/article/upload-image', $updData);
        // ファイルの存在チェック
        $id = $db->getFileLastID();
        $data = $db->getFile($id);
        $file = $data[0];
        $updFile = asset('storage/uploads/image/' . $file->year . '/' . $file->month . '/middle/' . $file->filename);
        $response
            ->assertStatus(200)
            ->assertJson(['success' => true, 'file' => $icatchPath, 'alt' => $alt])
            ->assertJson(['success' => true, 'file' => $updFile, 'alt' => $file->description]);

        // 同名ファイル保存（renameされる）
        $uploadedFile = UploadedFile::fake()->image('test.jpg');
        $alt = 'testjpg';
        $icatchPath = asset('storage/uploads/image/' . $year . '/' . $month . '/middle/' . $uploadedFile->name);
        $updData = [];
        $updData['fileToUpload'] = $uploadedFile;
        $updData['alt'] = $alt;
        $response = $this->post('/admin/article/upload-image', $updData);
        // ファイルの存在チェック
        $id = $db->getFileLastID();
        $data = $db->getFile($id);
        $file = $data[0];
        $updFile = asset('storage/uploads/image/' . $file->year . '/' . $file->month . '/middle/' . $file->filename);
        $response
            ->assertStatus(200)
            ->assertJson(['success' => true, 'file' => $updFile, 'alt' => $file->description])
            ->assertJsonMissing(['file' => $icatchPath]);

        // 保存したファイルの削除
        Storage::disk('public')->deleteDirectory('uploads');
    }
    /**
     * 画像アップロード 異常系
    */
    public function test_uploadImageErr()
    {
        // 保存したファイルの削除
        Storage::disk('public')->deleteDirectory('uploads');

        $this->dummyAdminLogin();

        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');

        // ファイル名空
        $updData = [];
        $updData['fileToUpload'] = '';
        $updData['alt'] = '';
        $response = $this->post('/admin/article/upload-image', $updData);
        $response
            ->assertStatus(200)
            ->assertSee(false);

        // 容量オーバー
        $maxSize = config('umekoset.max_filesize') + 1;
        $uploadedFile = UploadedFile::fake()->image('limit.jpg')->size($maxSize);
        $updData = [];
        $updData['fileToUpload'] = $uploadedFile;
        $updData['alt'] = '';
        $response = $this->post('/admin/article/upload-image', $updData);
        $response
            ->assertStatus(422)
            ->assertJson(['err' => 'ファイルは' . config('umekoset.max_filesize') . 'kbyte以内にしてください。']);

        // ファイル名200文字（OK）
        $str = '';
        for($i=1; $i<=200; $i++) {
            $str .= 'a';
        }
        $uploadedFile = UploadedFile::fake()->image($str . '.jpg');
        $alt = 'testjpg';
        $icatchPath = asset('storage/uploads/image/' . $year . '/' . $month . '/middle/' . $uploadedFile->name);
        $updData = [];
        $updData['fileToUpload'] = $uploadedFile;
        $updData['alt'] = $alt;
        $response = $this->post('/admin/article/upload-image', $updData);
        // ファイルの存在チェック
        $db = new SaveFile();
        $id = $db->getFileLastID();
        $data = $db->getFile($id);
        $file = $data[0];
        $updFile = asset('storage/uploads/image/' . $file->year . '/' . $file->month . '/middle/' . $file->filename);
        $response
            ->assertStatus(200)
            ->assertJson(['success' => true, 'file' => $icatchPath, 'alt' => $alt])
            ->assertJson(['success' => true, 'file' => $updFile, 'alt' => $file->description]);

        // ファイル名201文字
        $uploadedFile = UploadedFile::fake()->image($str . 'A.jpg');
        $alt = 'testjpg';
        $icatchPath = asset('storage/uploads/image/' . $year . '/' . $month . '/middle/' . $uploadedFile->name);
        $updData = [];
        $updData['fileToUpload'] = $uploadedFile;
        $updData['alt'] = $alt;
        $response = $this->post('/admin/article/upload-image', $updData);
        $response
            ->assertStatus(422)
            ->assertJson(['err' => '200字以内で入力してください。']);

        // alt 255文字（OK）
        $str = '';
        for($i=1; $i<=255; $i++) {
            $str .= 'a';
        }
        $uploadedFile = UploadedFile::fake()->image('testalt.jpg');
        $alt = $str;
        $icatchPath = asset('storage/uploads/image/' . $year . '/' . $month . '/middle/' . $uploadedFile->name);
        $updData = [];
        $updData['fileToUpload'] = $uploadedFile;
        $updData['alt'] = $alt;
        $response = $this->post('/admin/article/upload-image', $updData);
        // ファイルの存在チェック
        $db = new SaveFile();
        $id = $db->getFileLastID();
        $data = $db->getFile($id);
        $file = $data[0];
        $updFile = asset('storage/uploads/image/' . $file->year . '/' . $file->month . '/middle/' . $file->filename);
        $response
            ->assertStatus(200)
            ->assertJson(['success' => true, 'file' => $icatchPath, 'alt' => $alt])
            ->assertJson(['success' => true, 'file' => $updFile, 'alt' => $file->description]);

        // alt 256文字
        $uploadedFile = UploadedFile::fake()->image('testalt.jpg');
        $alt = $str . 'あ';
        $icatchPath = asset('storage/uploads/image/' . $year . '/' . $month . '/middle/' . $uploadedFile->name);
        $updData = [];
        $updData['fileToUpload'] = $uploadedFile;
        $updData['alt'] = $alt;
        $response = $this->post('/admin/article/upload-image', $updData);
        // ファイルの存在チェック
        $db = new SaveFile();
        $id = $db->getFileLastID();
        $data = $db->getFile($id);
        $file = $data[0];
        $updFile = asset('storage/uploads/image/' . $file->year . '/' . $file->month . '/middle/' . $file->filename);
        $response
            ->assertStatus(422)
            ->assertJson(['err' => '255字以内で入力してください。']);

        // 指定外拡張子
        $uploadedFile = UploadedFile::fake()->image('noex.pdf');
        $updData = [];
        $updData['fileToUpload'] = $uploadedFile;
        $updData['alt'] = 'test';
        $response = $this->post('/admin/article/upload-image', $updData);
        $response
            ->assertStatus(422)
            ->assertJson(['err' => '拡張子は' . implode(' 、 ', config('umekoset.image_ex')) . ' のいずれかのファイルを指定してください。']);

        // 複数エラーメッセージ
        $maxSize = config('umekoset.max_filesize') + 1;
        $uploadedFile = UploadedFile::fake()->image('limit.jpg')->size($maxSize);
        $alt = $str . 'あ';
        $icatchPath = asset('storage/uploads/image/' . $year . '/' . $month . '/middle/' . $uploadedFile->name);
        $updData = [];
        $updData['fileToUpload'] = $uploadedFile;
        $updData['alt'] = $alt;
        $response = $this->post('/admin/article/upload-image', $updData);
        // ファイルの存在チェック
        $db = new SaveFile();
        $id = $db->getFileLastID();
        $data = $db->getFile($id);
        $file = $data[0];
        $updFile = asset('storage/uploads/image/' . $file->year . '/' . $file->month . '/middle/' . $file->filename);
        $response
            ->assertStatus(422)
            ->assertJson(['err' => 'ファイルは' . config('umekoset.max_filesize') . 'kbyte以内にしてください。 / 255字以内で入力してください。']);

        // 保存したファイルの削除
        Storage::disk('public')->deleteDirectory('uploads');
    }

    /**
     * プレビュー画面
     * 空POST OK、エラーはなし
     */
    public function test_previewAdmin()
    {
        // 通常想定される使い方
        // 保存したファイルの削除
        Storage::disk('public')->deleteDirectory('uploads');

        $this->dummyAdminLogin();
        $uploadedFile = UploadedFile::fake()->image('test.jpg');

        $icatch = 'data:' . mime_content_type($uploadedFile->getPathName()) . ';base64,' . base64_encode(file_get_contents($uploadedFile->getPathName()));

        $title = 'FeatureTestTitle';
        $contents = '<p>FeatureTestContents</p>';
        $status = config('umekoset.status_publish');
        $path = 'feature_test';
        $article_auth = config('umekoset.article_auth_admin');
        $seo = 'Fe2tureTestSeo';
        $category01 = 1;
        $category02 = 2;
        $category03 = 3;
        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');
        $day = Carbon::now()->format('d');
        $hour = Carbon::now()->format('h');
        $min = Carbon::now()->format('i');

        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['category'][] = $category01;
        $updData['category'][] = $category02;
        $updData['category'][] = $category03;
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $updData['icatch'] = $uploadedFile;
        $response = $this->post('/admin/article/preview', $updData);

        $response
            ->assertSee('<div class="preview-row">Preview</div>', false)
            ->assertSee('<img src="' . $icatch . '"', false)
            ->assertSee($title, false)
            ->assertSee($contents, false)
            ->assertSee($year . '/' . $month . '/' . $day . ' ' . $hour . ':' . $min, false)
            ->assertSee('カテゴリ01', false)
            ->assertSee('カテゴリ02', false)
            ->assertSee('カテゴリ03', false);

        // 編集画面にアクセスしてプレビュー
        $search = [];
        $id = 4;
        $search['id'] = $id;
        $db = new Article();
        $data = $db->getArticle($search);
        $data = $data[0];
        // 公開日時の設定
        $data->open_year = date('Y', strtotime($data->publish_at));
        $data->open_month = date('m', strtotime($data->publish_at));
        $data->open_day = date('d', strtotime($data->publish_at));
        $data->open_hour = date('H', strtotime($data->publish_at));
        $data->open_min = date('i', strtotime($data->publish_at));
        $data->open_seconds = date('s', strtotime($data->publish_at));
        // アイキャッチ画像の設定
        if($data->icatch) {
            $icatch = asset('storage/uploads/image/' . $data->icatch_y . '/' . $data->icatch_m . '/large/' . $data->icatch_file);
        }
        // カテゴリ情報の取得
        $updData = [];
        $relCatDb = new RelatedCategory();
        $relData = $relCatDb->getCategories($id);
        if(!empty($relData)) {
            foreach($relData as $reld) {
                 $updData['category'][] = $reld->category_id;
            }
        }
        $updData['post_title'] = $data->title;
        $updData['trumbowyg-editor'] = $data->contents;
        $updData['status'] = $data->status;
        $updData['path'] = $data->path;
        $updData['article_auth'] = $data->article_auth;
        $updData['seo_description'] = $data->seo_description;
        $updData['open_year'] = $data->open_year;
        $updData['open_month'] = $data->open_month;
        $updData['open_day'] = $data->open_day;
        $updData['open_hour'] = $data->open_hour;
        $updData['open_min'] = $data->open_min;
        $updData['save_icatch'] = $data->icatch;
        $response = $this->post('/admin/article/preview', $updData);
        $response
            ->assertSee('<div class="preview-row">Preview</div>', false)
            ->assertSee('<img src="' . $icatch . '"', false)
            ->assertSee($data->title, false)
            ->assertSee($data->contents, false)
            ->assertSee($data->open_year . '/' . $data->open_month . '/' . $data->open_day . ' ' . $data->open_hour . ':' . $data->open_min, false);
        if(!empty($relData)) {
            foreach($relData as $reld) {
             $response->assertSee($reld->category_name, false);
            }
        }

        // 空状態でプレビューアクセス
        $title = '';
        $contents = '';
        $status = config('umekoset.status_publish');
        $path = '';
        $article_auth = config('umekoset.article_auth_admin');
        $seo = '';
        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');
        $day = Carbon::now()->format('d');
        $hour = Carbon::now()->format('h');
        $min = Carbon::now()->format('i');

        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $updData['icatch'] = $uploadedFile;
        $response = $this->post('/admin/article/preview', $updData);

        $response
            ->assertSee('<div class="preview-row">Preview</div>', false)
            ->assertSee($title, false)
            ->assertSee($contents, false)
            ->assertSee($year . '/' . $month . '/' . $day . ' ' . $hour . ':' . $min, false)
            ->assertDontSee('<ul class="article_category">', false);

        // ユーザーも使用できることの確認
        $this->dummyUserLogin();
        $uploadedFile = UploadedFile::fake()->image('test_user.jpg');

        $icatch = 'data:' . mime_content_type($uploadedFile->getPathName()) . ';base64,' . base64_encode(file_get_contents($uploadedFile->getPathName()));

        $title = 'FeatureTestTitle';
        $contents = '<p>FeatureTestContents</p>';
        $status = config('umekoset.status_publish');
        $path = 'feature_test';
        $article_auth = config('umekoset.article_auth_admin');
        $seo = 'Fe2tureTestSeo';
        $category01 = 1;
        $category02 = 2;
        $category03 = 3;
        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');
        $day = Carbon::now()->format('d');
        $hour = Carbon::now()->format('h');
        $min = Carbon::now()->format('i');

        $updData = [];
        $updData['post_title'] = $title;
        $updData['trumbowyg-editor'] = $contents;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['category'][] = $category01;
        $updData['category'][] = $category02;
        $updData['category'][] = $category03;
        $updData['seo_description'] = $seo;
        $updData['open_year'] = $year;
        $updData['open_month'] = $month;
        $updData['open_day'] = $day;
        $updData['open_hour'] = $hour;
        $updData['open_min'] = $min;
        $updData['icatch'] = $uploadedFile;
        $response = $this->post('/admin/article/preview', $updData);

        $response
            ->assertSee('<div class="preview-row">Preview</div>', false)
            ->assertSee('<img src="' . $icatch . '"', false)
            ->assertSee($title, false)
            ->assertSee($contents, false)
            ->assertSee($year . '/' . $month . '/' . $day . ' ' . $hour . ':' . $min, false)
            ->assertSee('カテゴリ01', false)
            ->assertSee('カテゴリ02', false)
            ->assertSee('カテゴリ03', false);
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
