<?php
/**
 * /app/Models/Article.phpのテスト
 */
namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase; // テスト用データを自動で元に戻す

class ArticleTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $seed = true;

    /**
     * getList
     * オブジェクトを取得していること
     */
    public function test_ArticleGetOObject()
    {
        $db = new Article();
        $articles = $db->getList([]);
        $this->assertTrue(is_object($articles));
    }
    /**
     * 必要な項目を取得していること
     */
    public function test_ArticleGetCols()
    {
        $db = new Article();
        $articles = $db->getList([]);

        $cols = ['id', 'title', 'contents', 'user_id', 'status', 'path', 'updated_user_id', 'publish_at', 'updated_at', 'user_name', 'updated_user'];
        $this->assertSame($cols, array_keys((array)$articles[0]));
    }
    /**
     * 検索条件「ステータス：公開中」の情報を取得していること
     */
    public function test_ArticleSearchPublishStatus()
    {
        $params = [];
        $params['status'] = config('umekoset.status_publish');
        $db = new Article();
        $articles = $db->getList($params);

        // ステータス「公開中」のもののデータだけかどうかを確認する
        foreach($articles as $data) {
            $this->assertEquals($data->status, config('umekoset.status_publish'));
        }
    }

    /**
     * 検索条件「ステータス：非公開」
     */
    public function test_ArticleSearchUnpublishStatus()
    {
        $params = [];
        $params['status'] = config('umekoset.status_private');
        $db = new Article();
        $articles = $db->getList($params);

        // ステータス「公開中」のもののデータだけかどうかを確認する
        foreach($articles as $data) {
            $this->assertEquals($data->status, config('umekoset.status_private'));
        }
    }

    /**
     * 検索条件「ステータス：下書き」
     */
    public function test_ArticleSearchDraftStatus()
    {
        $params = [];
        $params['status'] = config('umekoset.status_draft');
        $db = new Article();
        $articles = $db->getList($params);

        // ステータス「下書き」のもののデータだけかどうかを確認する
        foreach($articles as $data) {
            $this->assertEquals($data->status, config('umekoset.status_draft'));
        }
    }


    /**
     * getArticle
     * 配列を取得していること
     */
    public function test_ArticleGetArray()
    {
        $db = new Article();
        $article = $db->getArticle(['id' => 1]);
        $this->assertTrue(is_array($article));
    }
    /**
     * 指定IDのデータを取得していること
     */
    public function test_ArticleGetData()
    {
        $db = new Article();
        $data = $db->getArticle(['id' => 2]);
        $article = $data[0];

        $this->assertSame($article->title, 'テスト投稿タイトル02');
        $this->assertSame($article->contents, '<p>これはテスト投稿です</p>');
        $this->assertSame($article->user_id, 2);
        $this->assertSame($article->status, 2);
        $this->assertSame($article->path, 'test02');
        $this->assertSame($article->article_auth, config('umekoset.article_auth_creator'));
        $this->assertSame($article->updated_user_id, 2);
    }
    /**
     * 空IDのときは何も取得しないこと
     */
    public function test_ArticleGetEmptyData()
    {
        $db = new Article();
        $article = $db->getArticle(['id' => '']);

        $this->assertEmpty($article);
    }
    /**
     * 存在しないIDのときは空になること
     */
    public function test_ArticleGetNoData()
    {
        $db = new Article();
        $article = $db->getArticle(['id' => 999]);

        $this->assertEmpty($article);
    }

    /**
     * addArticle
     * 記事を追加する
     */
    public function test_ArticleAdd()
    {
        $title = $this->faker->realText(30);
        $str = $this->faker->realText(500);
        $description = $this->faker->realText(100);
        $status= mt_rand(1, 3);
        $path = '/test/' . $this->faker->unique->randomNumber(5);
        $article_auth = mt_rand(1, 2);
        $date = date('Y-m-d H:i:s');
        $update = $date;
        $user_id = mt_rand(1, 5);

        $addData = [];
        $addData['title'] = $title;
        $addData['contents'] = $str;
        $addData['status'] = $status;
        $addData['path'] = $path;
        $addData['article_auth'] = $article_auth;
        $addData['seo_description'] = $description;
        $addData['user_id'] = $user_id;
        $addData['created_at'] = $date;
        $addData['updated_user_id'] = $user_id;
        $addData['updated_at'] = $update;

        $db = new Article();
        $id = $db->addArticle($addData);
        $this->assertNotNull($id);
        if($id != null) {
            $search = $this->setDefaultParams($id, 1);
            $db = new Article();
            $data = $db->getArticle($search);
            $article = $data[0];
            // データベースから取得したデータと登録したデータがすべて一致することを確認
            $this->assertSame($article->id, $id);
            $this->assertSame($article->title, $title);
            $this->assertSame($article->contents, $str);
            $this->assertSame($article->status, $status);
            $this->assertSame($article->path, $path);
            $this->assertSame($article->article_auth, $article_auth);
            $this->assertSame($article->seo_description, $description);
            $this->assertSame($article->user_id, $user_id);
            $this->assertSame($article->created_at, $date);
            $this->assertSame($article->updated_user_id, $user_id);
            $this->assertSame($article->updated_at, $update);
        }
    }

    /**
     * updateArticle
     * 記事を更新する（すべて）
     */
    public function test_ArticleEditAll()
    {
        $id = 2;
        $title = $this->faker->realText(30);
        $str = $this->faker->realText(500);
        $description = $this->faker->realText(100);
        $status= 1;
        $path = '/test/' . $this->faker->randomNumber(4) . '/' . $this->faker->randomNumber(4);
        $update = date('Y-m-d H:i:s', strtotime('+1day'));
        $user_id = 6;

        // 更新前データ
        $db = new Article();
        $beforeData = $db->getArticle(['id' => $id]);
        $before = $beforeData[0];

        $article_auth = ($before->article_auth == config('umekoset.article_auth_admin')) ? config('umekoset.article_auth_creator') : config('umekoset.article_auth_admin');;

        $updData = [];
        $updData['id'] = $id;
        $updData['title'] = $title;
        $updData['contents'] = $str;
        $updData['status'] = $status;
        $updData['path'] = $path;
        $updData['article_auth'] = $article_auth;
        $updData['seo_description'] = $description;
        $updData['updated_user_id'] = $user_id;
        $updData['updated_at'] = $update;

        $result = $db->updateArticle($updData);
        // 更新成功していることを確認
        $this->assertSame(1, $result);

        $afterData = $db->getArticle(['id' => $id]);
        $after = $afterData[0];

        // 更新した内容がPOSTしたものと一致すること
        $this->assertSame($after->title, $title);
        $this->assertSame($after->contents, $str);
        $this->assertSame($after->status, $status);
        $this->assertSame($after->path, $path);
        $this->assertSame($after->article_auth, $article_auth);
        $this->assertSame($after->seo_description, $description);
        $this->assertSame($after->updated_user_id, $user_id);
        $this->assertSame($after->updated_at, $update);

        // 更新前データとデータベースから取得したデータが更新部分以外は一致することを確認
        $this->assertSame($before->id, $after->id);
        $this->assertSame($before->user_id, $after->user_id);
        $this->assertSame($before->created_at, $after->created_at);
        // 不一致項目（更新された項目）
        $this->assertNotSame($before->contents, $after->contents);
        $this->assertNotSame($before->status, $after->status);
        $this->assertNotSame($before->path, $after->path);
        $this->assertNotSame($before->article_auth, $after->article_auth);
        $this->assertNotSame($before->seo_description, $description);
        $this->assertNotSame($before->updated_user_id, $after->updated_user_id);
        $this->assertNotSame($before->updated_at, $after->updated_at);
    }
    /**
     * 記事を編集する（タイトル、本文）
     */
    public function test_ArticleEdit()
    {
        $id = 3;
        $title = $this->faker->realText(30);
        $str = $this->faker->realText(500);
        $update = date('Y-m-d H:i:s', strtotime('+1day'));
        $user_id = 5;

        // 更新前データ
        $db = new Article();
        $beforeData = $db->getArticle(['id' => $id]);
        $before = $beforeData[0];

        $updData = [];
        $updData['id'] = $id;
        $updData['title'] = $title;
        $updData['contents'] = $str;
        $updData['updated_user_id'] = $user_id;
        $updData['updated_at'] = $update;

        $result = $db->updateArticle($updData);
        // 更新成功していることを確認
        $this->assertSame(1, $result);

        $afterData = $db->getArticle(['id' => $id]);
        $after = $afterData[0];

        // 更新した内容がPOSTしたものと一致すること
        $this->assertSame($after->title, $title);
        $this->assertSame($after->contents, $str);
        $this->assertSame($after->updated_user_id, $user_id);
        $this->assertSame($after->updated_at, $update);

        // 更新前データとデータベースから取得したデータが更新部分以外は一致することを確認
        $this->assertSame($before->id, $id);
        $this->assertSame($before->status, $after->status);
        $this->assertSame($before->path, $after->path);
        $this->assertSame($before->user_id, $after->user_id);
        $this->assertSame($before->created_at, $after->created_at);
        $this->assertSame($before->updated_user_id, $after->user_id);
        // 不一致項目 タイトル、本文、更新ユーザー、更新日
        $this->assertNotSame($before->title, $after->title);
        $this->assertNotSame($before->contents, $after->contents);
        $this->assertNotSame($before->updated_user_id, $after->updated_user_id);
        $this->assertNotSame($before->updated_at, $after->updated_at);
    }
    /**
     * id空で記事を編集しようとする
     */
    public function test_ArticleEditErr()
    {
        $id = 1;
        $title = $this->faker->realText(30);
        $str = $this->faker->realText(500);
        $update = date('Y-m-d H:i:s', strtotime('+1day'));
        $user_id = 2;

        $db = new Article();
        $beforeData = $db->getArticle(['id' => $id]);
        $before = $beforeData[0];

        $updData = [];
        $updData['title'] = $title;
        $updData['contents'] = $str;
        $updData['updated_user_id'] = $user_id;
        $updData['updated_at'] = $update;

        $result = $db->updateArticle($updData);
        // 更新されなかったことを確認
        $this->assertFalse($result);

        $afterData = $db->getArticle(['id' => $id]);
        $after = $afterData[0];

        // 更新前データとテスト後データが変わらないことを確認
        $this->assertSame($before->id, $after->id);
        $this->assertSame($before->title, $after->title);
        $this->assertSame($before->contents, $after->contents);
        $this->assertSame($before->status, $after->status);
        $this->assertSame($before->path, $after->path);
        $this->assertSame($before->user_id, $after->user_id);
        $this->assertSame($before->created_at, $after->created_at);
        $this->assertSame($before->updated_user_id, $after->updated_user_id);
        $this->assertSame($before->updated_at, $after->updated_at);
    }

    /**
     * deleteArticle
     * 記事を削除する
     */
    public function test_ArticleDelete()
    {
        $id = 10;

        $db = new Article();
        $result = $db->deleteArticle($id);
        // 削除成功していることを確認
        $this->assertSame(1, $result);

        // データがないことを確認
        $data = $db->getArticle($id);
        $this->assertEmpty($data);
    }
    /**
     * id空で記事を削除しようとする
     */
    public function test_ArticleDeleteEmpty()
    {
        $db = new Article();
        $result = $db->deleteArticle('');
        // 失敗していることを確認
        $this->assertFalse($result);
    }

    /**
     * searchRelatedFile
     * 記事内に指定のファイル名が含まれるかを検索する
     */
    public function test_searchRelatedFile()
    {
        $db = new Article();
        $data = $db->searchRelatedFile(1);
        $this->assertSame(1, count($data));
        $this->assertSame(4, $data[0]->id);

        $data = $db->searchRelatedFile(1, 'test.jpg');
        $this->assertSame(2, count($data));
        $this->assertSame(4, $data[0]->id);
        $this->assertSame(8, $data[1]->id);

        $data = $db->searchRelatedFile('', 'test04.jpg');
        $this->assertSame(1, count($data));
        $this->assertSame(7, $data[0]->id);

        $data = $db->searchRelatedFile(4);
        $this->assertSame(0, count($data));

        // id、filenameの両方が空の時は空を返す
        $data = $db->searchRelatedFile();
        $this->assertSame(0, count($data));
    }

    /**
     * getRecentList
     * 最近更新されたデータを取得する
     * 管理画面、最近一週間以内
     */
    public function test_getRecentList()
    {
        $db = new Article();
        $data = $db->getRecentList();
        $this->assertSame(8, count($data));

        // 管理者
        $search = [];
        $search['article_auth'] = config('umekoset.article_auth_admin');
        $search['user_id'] = 1;
        $data = $db->getRecentList($search);
        $this->assertSame(1, count($data));

        // 一般ユーザー01
        $search = [];
        $search['article_auth'] = config('umekoset.article_auth_creator');
        $search['user_id'] = 2;
        $data = $db->getRecentList($search);
        $this->assertSame(1, count($data));

        // 一般ユーザー05
        $search = [];
        $search['article_auth'] = config('umekoset.article_auth_creator');
        $search['user_id'] = 6;
        $data = $db->getRecentList($search);
        $this->assertSame(0, count($data));
    }

    /**
     * getPublishList
     * 公開用記事データを取得
     */
    public function test_getPublishList()
    {
        $db = new Article();
        $data = $db->getPublishList();
        $num = !empty(config('umekoset.article_index_num')) ? config('umekoset.article_index_num') : config('umekoset.default_index_num');
        $this->assertSame($num, count($data));

        $cols = [
            'id',
            'title',
            'contents',
            'path',
            'icatch',
            'publish_at',
            'user_name',
            'icatch_y',
            'icatch_m',
            'icatch_file',
            'description'
        ];

        // 公開日時が本日のものか（データベースを毎回更新する都合、必ず本日付の物になる）
        $date = date('Y-m-d');
        foreach($data as $d) {
            $this->assertMatchesRegularExpression('/' . $date . '/', $d->publish_at);
            $this->assertSame($cols, array_keys((array)$d));
        }
    }

    /**
     * searchArticle
     * 記事情報の検索（公開画面用）
     */
    public function test_searchArticle()
    {
        $cols = [
            'id',
            'title',
            'contents',
            'status',
            'article_auth',
            'path',
            'icatch',
            'seo_description',
            'publish_at',
            'user_id',
            'created_at',
            'updated_user_id',
            'updated_at',
            'user_name',
            'updated_user',
            'icatch_y',
            'icatch_m',
            'icatch_file',
        ];
        $db = new Article();
        $data = $db->searchArticle();
        $this->assertEmpty($data);

        // 次の記事、前の記事の検索は必ず1件のみ取得
        $search = [];
        $search['start_date'] = date('Y-m-d', strtotime('-1day'));
        $data = $db->searchArticle($search);
        $this->assertSame(1, count($data));
        $this->assertSame($cols, array_keys((array)$data[0]));

        $search = [];
        $search['end_date'] = date('Y-m-d', strtotime('+1day'));
        $data = $db->searchArticle($search);
        $this->assertSame(1, count($data));

        $search = [];
        $search['path'] = 'test01';
        $data = $db->searchArticle($search);
        $this->assertSame(1, count($data));
        $this->assertSame('テスト投稿タイトル01', $data[0]->title);
        $this->assertSame('<p>これはテスト投稿です</p>', $data[0]->contents);
        $this->assertSame(1, $data[0]->id);

        // 非公開データは取得できない
        $search = [];
        $search['path'] = 'test02';
        $data = $db->searchArticle($search);
        $this->assertSame(0, count($data));

        // 最近更新された記事
        // データベース状態によって件数が変わるが、最低1件以上は存在する
        $search = [];
        $search['recentday'] = config('umekoset.sidebar_recent_day');
        $num = !empty(config('umekoset.sidebar_num')) ? config('umekoset.sidebar_num') : config('umekoset.default_index_num');
        $data = $db->searchArticle($search);
        $this->assertGreaterThanOrEqual(1, count($data));
        // 更新日時が本日のものか（データベースを毎回更新する都合、ここにあたるのは必ず本日付の物になる）
        $date = date('Y-m-d');
        foreach($data as $d) {
            $this->assertMatchesRegularExpression('/' . $date . '/', $d->publish_at);
        }
    }

    /**
     * searchArticlePager
     * 次のページ、前のページ検索用
     * 公開中の記事を検索する（細かいところはコントローラで処理する）
     */
    public function test_searchArticlePager()
    {
        $cols = [
            'id',
            'title',
            'contents',
            'status',
            'article_auth',
            'path',
            'icatch',
            'seo_description',
            'publish_at',
            'user_id',
            'created_at',
            'updated_user_id',
            'updated_at',
            'user_name',
            'updated_user',
            'icatch_y',
            'icatch_m',
            'icatch_file',
        ];
        $db = new Article();
        $data = $db->searchArticlePager();
        $this->assertGreaterThanOrEqual(1, count($data));
        $this->assertSame($cols, array_keys((array)$data[0]));
    }

    /**
     * searchArticleDate
     * 日付アーカイブ用取得
     */
    public function test_searchArticleDate()
    {
        $db = new Article();
        // 年月（現在）
        $year = intval(date('Y'));
        $month = intval(date('m'));
        $search['start_date'] = $year . '-' . (empty($month) ? '01' : sprintf('%02d', $month)) . '-01 00:00:00';
        $search['end_date'] = (empty($month) ? $year+1 : (($month == 12) ? $year+1 : $year)) . '-' . (empty($month) ? '01' : sprintf('%02d', (($month == 12) ? 1 : $month+1))) . '-01 00:00:00';

        $data = $db->searchArticleDate($search);
        $this->assertGreaterThanOrEqual(1, count($data));

        // 年月（未来）
        $year = intval(date('Y'));
        $month = intval(date('m', strtotime('+1 month')));
        $search['start_date'] = $year . '-' . (empty($month) ? '01' : sprintf('%02d', $month)) . '-01 00:00:00';
        $search['end_date'] = (empty($month) ? $year+1 : (($month == 12) ? $year+1 : $year)) . '-' . (empty($month) ? '01' : sprintf('%02d', (($month == 12) ? 1 : $month+1))) . '-01 00:00:00';

        $data = $db->searchArticleDate($search);
        $this->assertEquals(0, count($data));

        // 年月（存在しない）
        $year = 5;
        $month = 13;
        $search['start_date'] = $year . '-' . (empty($month) ? '01' : sprintf('%02d', $month)) . '-01 00:00:00';
        $search['end_date'] = (empty($month) ? $year+1 : (($month == 12) ? $year+1 : $year)) . '-' . (empty($month) ? '01' : sprintf('%02d', (($month == 12) ? 1 : $month+1))) . '-01 00:00:00';

        $data = $db->searchArticleDate($search);
        $this->assertEquals(0, count($data));

        // 年のみ（現在）
        $year = intval(date('Y'));
        $month = '';
        $search['start_date'] = $year . '-' . (empty($month) ? '01' : sprintf('%02d', $month)) . '-01 00:00:00';
        $search['end_date'] = (empty($month) ? $year+1 : (($month == 12) ? $year+1 : $year)) . '-' . (empty($month) ? '01' : sprintf('%02d', (($month == 12) ? 1 : $month+1))) . '-01 00:00:00';

        $data = $db->searchArticleDate($search);
        $this->assertGreaterThanOrEqual(1, count($data));

        // 年のみ（未来）
        $year = intval(date('Y', strtotime('+1 year')));
        $month = '';
        $search['start_date'] = $year . '-' . (empty($month) ? '01' : sprintf('%02d', $month)) . '-01 00:00:00';
        $search['end_date'] = (empty($month) ? $year+1 : (($month == 12) ? $year+1 : $year)) . '-' . (empty($month) ? '01' : sprintf('%02d', (($month == 12) ? 1 : $month+1))) . '-01 00:00:00';

        $data = $db->searchArticleDate($search);
        $this->assertEquals(0, count($data));

        // 年のみ（存在しない）
        $year = 1;
        $month = '';
        $search['start_date'] = $year . '-' . (empty($month) ? '01' : sprintf('%02d', $month)) . '-01 00:00:00';
        $search['end_date'] = (empty($month) ? $year+1 : (($month == 12) ? $year+1 : $year)) . '-' . (empty($month) ? '01' : sprintf('%02d', (($month == 12) ? 1 : $month+1))) . '-01 00:00:00';

        $data = $db->searchArticleDate($search);
        $this->assertEquals(0, count($data));
    }

    /**
     * setDefaultParams
     * ログインユーザーによるデフォルトの検索条件をセットする
     * @access private
     * @param $id
     * @return $search 検索条件
     */
    private function setDefaultParams($id='', $auth=1, $user_id='')
    {
        $search = [];
        $search['id'] = $id;
        // 管理者ログイン以外は編集権限が「管理者+作成者」のもののみとする
        if($auth != 1) {
            $search['article_auth'] = config('umekoset.article_auth_creator');
            $search['user_id'] = $user_id;
        }
        return $search;
    }
}
