<?php
/**
 * 公開側表示機能の試験
 */
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    /**
     * 記事の公開ページに認証が必要ないことを確認する
     */
    public function test_access()
    {
        $year = date('Y');
        $month = date('m');

        $response = $this->get('/');
        $response
            ->assertStatus(200)
            ->assertViewIs('public.index');

        $response = $this->get('/' . $year . '/' . $month . '/test01');
        $response
            ->assertStatus(200)
            ->assertViewIs('public.article');

        $response = $this->get('/category/01');
        $response
            ->assertStatus(200)
            ->assertViewIs('public.category');

        // 存在しないページは404
        $response = $this->get('/category/010');
        $response->assertStatus(404);

        // 存在しないページは404
        $response = $this->get('/2021/12/test010');
        $response->assertStatus(404);
    }

    /**
     * トップページ
     */
    public function test_topAccess()
    {
        $year = date('Y');
        $month = date('n');

        $response = $this->get('/');
        $response
            ->assertStatus(200)
            ->assertSee('オープンソースのブログCMS「梅子」',false)
            ->assertSee('<h2>最新の記事</h2>', false)
            ->assertSee('<h3>カテゴリー</h3>', false)
            ->assertSee('<h3>最近更新された記事</h3>', false)
            ->assertSee('<h3>アーカイブ</h3>', false)
            ->assertSee('/date/' . $year . '/' . $month . '">' . $year . '年' . sprintf('%d', $month) . '月', false)
            ->assertSee('<meta name="description" content="オープンソースのブログCMS「梅子」" />', false)
            ->assertSee('<meta property="og:title" content="梅子-Umeko-" />', false)
            ->assertSee('<meta property="og:type" content="website" />', false)
            ->assertSee('<meta property="og:url" content="' . url('/') . '" />', false)
            ->assertSee('<meta property="og:image" content="' . url('/images/umeko-logo.png') . '" />', false)
            ->assertSee('<meta property="og:site_name" content="梅子-Umeko-" />', false)
            ->assertSee('<meta property="og:description" content="オープンソースのブログCMS「梅子」" />', false)
            ->assertSee('<meta property="article:published_time" content="', false)
            ->assertSee('<meta property="article:modified_time" content="', false);
    }

    /**
     * 記事ページ
     */
    public function test_articleAccess()
    {
        $year = date('Y');
        $month = date('n');

        $response = $this->get('/' . $year . '/' . sprintf('%02d', $month) . '/test01');
        $response
            ->assertStatus(200)
            ->assertDontSee('<div class="preview-row">Preview</div>', false)
            ->assertSee('オープンソースのブログCMS「梅子」',false)
            ->assertSeeText('テスト投稿タイトル01')
            ->assertSeeText('カテゴリ01')
            ->assertSeeText('カテゴリ02')
            ->assertSeeText('次の記事')
            ->assertDontSeeText('前の記事')
            ->assertDontSee('<h2>関連記事</h2>', false)
            ->assertSee('<h3>カテゴリー</h3>', false)
            ->assertSee('<h3>最近更新された記事</h3>', false)
            ->assertSee('<h3>アーカイブ</h3>', false)
            ->assertSee('/date/' . $year . '/' . $month . '">' . $year . '年' . sprintf('%d', $month) . '月', false)
            ->assertSee('<meta name="description" content="" />', false)
            ->assertSee('<meta property="og:title" content="テスト投稿タイトル01 | 梅子-Umeko-" />', false)
            ->assertSee('<meta property="og:type" content="website" />', false)
            ->assertSee('<meta property="og:url" content="' . url('/' . $year . '/' . sprintf('%02d', $month) . '/test01') . '" />', false)
            ->assertSee('<meta property="og:site_name" content="梅子-Umeko-" />', false)
            ->assertSee('<meta property="og:description" content="" />', false)
            ->assertSee('<meta property="article:published_time" content="', false)
            ->assertSee('<meta property="article:modified_time" content="', false);
    }

    /**
     * カテゴリページ
     */
    public function test_categoryAccess()
    {
        $year = date('Y');
        $month = date('n');

        $response = $this->get('/category/01');
        $response
            ->assertStatus(200)
            ->assertSee('オープンソースのブログCMS「梅子」',false)
            ->assertSeeText('テスト投稿タイトル01')
            ->assertSeeText('カテゴリ01')
            ->assertSeeText('カテゴリ02')
            ->assertDontSeeText('次の記事')
            ->assertDontSeeText('前の記事')
            ->assertDontSee('<h2>関連記事</h2>', false)
            ->assertSee('<h3>カテゴリー</h3>', false)
            ->assertSee('<h3>最近更新された記事</h3>', false)
            ->assertSee('<h3>アーカイブ</h3>', false)
            ->assertSee('/date/' . $year . '/' . $month . '">' . $year . '年' . sprintf('%d', $month) . '月', false)
            ->assertSee('<meta name="description" content="カテゴリー名「01」の一覧です。" />', false)
            ->assertSee('<meta property="og:title" content="01 の記事 | 梅子-Umeko-" />', false)
            ->assertSee('<meta property="og:type" content="website" />', false)
            ->assertSee('<meta property="og:url" content="' . url('/category/01') . '" />', false)
            ->assertSee('<meta property="og:image" content="' . url('/images/umeko-logo.png') . '" />', false)
            ->assertSee('<meta property="og:site_name" content="梅子-Umeko-" />', false)
            ->assertSee('<meta property="og:description" content="カテゴリー名「01」の一覧です。" />', false)
            ->assertSee('<meta property="article:published_time" content="', false)
            ->assertSee('<meta property="article:modified_time" content="', false);

        $response = $this->get('/category/02');
        $response
            ->assertStatus(200)
            ->assertSee('オープンソースのブログCMS「梅子」',false)
            ->assertSeeText('テスト投稿タイトル01')
            ->assertSeeText('カテゴリ01')
            ->assertSeeText('カテゴリ02')
            ->assertDontSeeText('次の記事')
            ->assertDontSeeText('前の記事')
            ->assertDontSee('<h2>関連記事</h2>', false)
            ->assertSee('<h3>カテゴリー</h3>', false)
            ->assertSee('<h3>最近更新された記事</h3>', false)
            ->assertSee('<h3>アーカイブ</h3>', false)
            ->assertSee('/date/' . $year . '/' . $month . '">' . $year . '年' . sprintf('%d', $month) . '月', false);
    }

    /**
     * 日付アーカイブページ
     */
    public function test_dateAccess()
    {
        $year = date('Y');
        $month = date('n');

        // 年月
        $response = $this->get('/date/' . $year . '/' . $month);
        $response
            ->assertStatus(200)
            ->assertSee('オープンソースのブログCMS「梅子」',false)
            ->assertSee('<span>1</span>', false)
            ->assertSee('<h2>' . $year . '年' . sprintf('%d', $month) . '月の記事</h2>', false)
            ->assertSee('<h3>カテゴリー</h3>', false)
            ->assertSee('<h3>最近更新された記事</h3>', false)
            ->assertSee('<h3>アーカイブ</h3>', false)
            ->assertSee('/date/' . $year . '/' . $month . '">' . $year . '年' . sprintf('%0d', $month) . '月', false)
            ->assertSee('<meta name="description" content="' . $year . '年' . $month . '月の記事一覧です。" />', false)
            ->assertSee('<meta property="og:title" content="' . $year . '年' . $month . '月の記事 | 梅子-Umeko-" />', false)
            ->assertSee('<meta property="og:type" content="website" />', false)
            ->assertSee('<meta property="og:url" content="' . url('/date/' . $year . '/' . $month) . '" />', false)
            ->assertSee('<meta property="og:image" content="' . url('/images/umeko-logo.png') . '" />', false)
            ->assertSee('<meta property="og:site_name" content="梅子-Umeko-" />', false)
            ->assertSee('<meta property="og:description" content="' . $year . '年' . $month . '月の記事一覧です。" />', false)
            ->assertSee('<meta property="article:published_time" content="', false)
            ->assertSee('<meta property="article:modified_time" content="', false);

        $response = $this->get('/date/' . $year . '/' . $month . '?page=2');
        $response
            ->assertStatus(200)
            ->assertSee('オープンソースのブログCMS「梅子」',false)
            ->assertSee('<span>2</span>', false)
            ->assertSee('<h2>' . $year . '年' . sprintf('%d', $month) . '月の記事</h2>', false)
            ->assertSee('<h3>カテゴリー</h3>', false)
            ->assertSee('<h3>最近更新された記事</h3>', false)
            ->assertSee('<h3>アーカイブ</h3>', false)
            ->assertSee('/date/' . $year . '/' . $month . '">' . $year . '年' . sprintf('%0d', $month) . '月', false);

        // 年
        $year = date('Y');
        $month = '';

        $response = $this->get('/date/' . $year);
        $response
            ->assertStatus(200)
            ->assertSee('オープンソースのブログCMS「梅子」',false)
            ->assertSee('<h2>' . $year . '年の記事</h2>', false)
            ->assertSee('<h3>カテゴリー</h3>', false)
            ->assertSee('<h3>最近更新された記事</h3>', false)
            ->assertSee('<h3>アーカイブ</h3>', false)
            ->assertSee('<meta name="description" content="' . $year . '年の記事一覧です。" />', false)
            ->assertSee('<meta property="og:title" content="' . $year . '年の記事 | 梅子-Umeko-" />', false)
            ->assertSee('<meta property="og:type" content="website" />', false)
            ->assertSee('<meta property="og:url" content="' . url('/date/' . $year) . '" />', false)
            ->assertSee('<meta property="og:image" content="' . url('/images/umeko-logo.png') . '" />', false)
            ->assertSee('<meta property="og:site_name" content="梅子-Umeko-" />', false)
            ->assertSee('<meta property="og:description" content="' . $year . '年の記事一覧です。" />', false)
            ->assertSee('<meta property="article:published_time" content="', false)
            ->assertSee('<meta property="article:modified_time" content="', false);

        $response = $this->get('/date/' . $year . '?page=2');
        $response
            ->assertStatus(200)
            ->assertSee('オープンソースのブログCMS「梅子」',false)
            ->assertSee('<h2>' . $year . '年の記事</h2>', false)
            ->assertSee('<h3>カテゴリー</h3>', false)
            ->assertSee('<h3>最近更新された記事</h3>', false)
            ->assertSee('<h3>アーカイブ</h3>', false);

        // 年（存在しない形式）
        $year = 200;
        $response = $this->get('/date/' . $year);
        $response->assertStatus(404);

        $year = 'aaa';
        $response = $this->get('/date/' . $year);
        $response->assertStatus(404);
    }
}
