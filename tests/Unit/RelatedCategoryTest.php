<?php
/**
 * /app/Models/RelatedCategory.phpのテスト
 */
namespace Tests\Unit;

use Tests\TestCase;
use App\Models\RelatedCategory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase; // テスト用データを自動で元に戻す

class RelatedCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;
    /**
     * getCategory
     * 配列を取得していること
     */
    public function test_getCategoriesArray()
    {
        $id = 1;
        $db = new RelatedCategory();
        $categories = $db->getCategories($id);
        $this->assertTrue(is_array($categories));
    }
    /**
     * 指定IDのデータを取得していること
     */
    public function test_getCategoriesData()
    {
        $id = 1;
        $db = new RelatedCategory();
        $categories = $db->getCategories($id);
        $this->assertSame($categories[0]->rel_id, 1);
        $this->assertSame($categories[0]->category_id, 1);
        $this->assertSame($categories[1]->rel_id, 2);
        $this->assertSame($categories[1]->category_id, 2);
    }
    /**
     * 空IDのときは何も取得しないこと
     */
    public function test_categoryGetEmptyData()
    {
        $id = '';
        $db = new RelatedCategory();
        $categories = $db->getCategories($id);

        $this->assertEmpty($categories);
    }
    /**
     * 存在しないIDのときは空になること
     */
    public function test_ArticleGetNoData()
    {
        $db = new RelatedCategory();
        $categories = $db->getCategories(['id' => 999]);

        $this->assertEmpty($categories);
    }

    /**
     * addCategory
     * 記事に関係するカテゴリーを登録する
     */
    public function test_addCategory()
    {
        // 単登録
        $db = new RelatedCategory();
        $articleId = 99;
        $categoryId = 99;
        $date = date('Y-m-d H:i:s');
        $update = $date;

        $addData = [];
        $addData['article_id'] = $articleId;
        $addData['category_id'] = $categoryId;
        $addData['created_at'] = $date;
        $addData['updated_at'] = $update;

        $id = $db->addRelCategory($addData);
        $this->assertNotNull($id);
        if($id != null) {
            $data = $db->getRelCategory($id);
            $category = $data[0];
            // データベースから取得したデータと登録したデータがすべて一致することを確認
            $this->assertSame($category->id, $id);
            $this->assertSame($category->article_id, $articleId);
            $this->assertSame($category->category_id, $categoryId);
            $this->assertSame($category->created_at, $date);
            $this->assertSame($category->updated_at, $update);
        }

        // 複数登録
        $articleId = 100;
        $categoryIds = [];
        $categoryIds[] = 100;
        $categoryIds[] = 101;
        foreach($categoryIds as $cat) {
            $addData = [];
            $addData['article_id'] = $articleId;
            $addData['category_id'] = $cat;
            $addData['created_at'] = $date;
            $addData['updated_at'] = $update;

            $id = $db->addRelCategory($addData);
            $this->assertNotNull($id);
            if($id != null) {
                $data = $db->getRelCategory($id);
                $category = $data[0];
                // データベースから取得したデータと登録したデータがすべて一致することを確認
                $this->assertSame($category->id, $id);
                $this->assertSame($category->article_id, $articleId);
                $this->assertSame($category->category_id, $cat);
                $this->assertSame($category->created_at, $date);
                $this->assertSame($category->updated_at, $update);
            }
        }
    }

    /**
     * deleteCategory
     * 記事に関係するカテゴリを削除する
     */
    public function test_deleteCategory()
    {
        $id = 10;

        $db = new RelatedCategory();
        $result = $db->deleteRelCategory($id);
        // 削除成功していることを確認
        $this->assertSame(1, $result);

        // データがないことを確認
        $data = $db->getRelCategory(['id' => $id]);
        $this->assertEmpty($data);
    }
    /**
     * id空で削除しようとする
     */
    public function test_deleteCategoryEmpty()
    {
        $db = new RelatedCategory();
        $result = $db->deleteRelCategory('');
        // 失敗していることを確認
        $this->assertFalse($result);
    }
    /**
     * 存在しないidで記事を削除しようとする
     */
    public function test_deleteCategoryNoExist()
    {
        $db = new RelatedCategory();
        $result = $db->deleteRelCategory(999);
        // 失敗していることを確認
        $this->assertSame(0, $result);
    }

    /**
     * getRelCats
     * 指定のカテゴリIDでrelated_idを取得できるか
     */
    public function test_getRelCats()
    {
        $id = 3;

        $db = new RelatedCategory();
        $categories = $db->getRelCats($id);
        $this->assertSame($categories[0]->id, 3);
    }
    /**
     * カテゴリID空
     */
    public function test_getRelCatsEmpty()
    {
        $id = '';

        $db = new RelatedCategory();
        $categories = $db->getRelCats($id);
        $this->assertEmpty($categories);
    }
    /**
     * 存在しないカテゴリID
     */
    public function test_getRelCatsNoExits()
    {
        $id = 9999;

        $db = new RelatedCategory();
        $categories = $db->getRelCats($id);
        $this->assertEmpty($categories);
    }

    /**
     * getRelCatArticle
     * 指定のカテゴリIDで記事を取得できるか
     */
    public function test_getRelCatArticle()
    {
        $id = 3;

        $db = new RelatedCategory();
        $categories = $db->getRelCatArticle($id);
        $this->assertSame($categories[0]->title, 'テスト投稿タイトル02');

        $categories = (array)$categories[0];
        $cols = ['title', 'contents', 'status', 'path', 'icatch', 'icatch_y', 'icatch_m', 'icatch_file', 'updated_at'];
        $this->assertSame($cols, array_keys($categories));
    }
    /**
     * カテゴリID空
     */
    public function test_getRelCatArticleEmpty()
    {
        $id = '';

        $db = new RelatedCategory();
        $categories = $db->getRelCatArticle($id);
        $this->assertEmpty($categories);
    }
    /**
     * 存在しないカテゴリID
     */
    public function test_getRelCatArticleNoExits()
    {
        $id = 9999;

        $db = new RelatedCategory();
        $categories = $db->getRelCatArticle($id);
        $this->assertEmpty($categories);
    }

    /**
     * getRelCatNameArticle
     * 指定カテゴリ名の記事を検索する
     */
    public function test_getRelCatNameArticle()
    {
        $db = new RelatedCategory();
        $data = $db->getRelCatNameArticle();
        $this->assertEmpty($data);

        // カテゴリ名01で検索
        $name = '01';
        $num = !empty(config('umekoset.article_index_num')) ? config('umekoset.article_index_num') : config('umekoset.default_index_num');
        $data = $db->getRelCatNameArticle($name);
        $this->assertGreaterThanOrEqual(1, count($data));
        $this->assertLessThanOrEqual($num, count($data));

        $name = '0123456789';
        $data = $db->getRelCatNameArticle();
        $this->assertEmpty($data);
    }

    /**
     * getRelCatArticles
     * 指定カテゴリ配列の記事を取得する
     * 公開側投稿記事の表示で使用する
     */
    public function test_getRelCatArticles()
    {
        $db = new RelatedCategory();
        $data = $db->getRelCatArticles();
        $this->assertEmpty($data);

        $catIds = [1];
        $db = new RelatedCategory();
        $data = $db->getRelCatArticles($catIds);
        $this->assertEmpty($data);

        $articleId = 1;
        $db = new RelatedCategory();
        $data = $db->getRelCatArticles('', $articleId);
        $this->assertEmpty($data);

        $catIds = [1];
        $articleId = 2;
        $db = new RelatedCategory();
        $data = $db->getRelCatArticles($catIds, $articleId);
        $this->assertSame(1, count($data));

        $catIds = [1,2];
        $articleId = 2;
        $db = new RelatedCategory();
        $data = $db->getRelCatArticles($catIds, $articleId);
        $this->assertSame(1, count($data));

        // 表示中の記事は除外される
        $catIds = [1];
        $articleId = 1;
        $db = new RelatedCategory();
        $data = $db->getRelCatArticles($catIds, $articleId);
        $this->assertEmpty($data);

        $catIds = [1,2];
        $articleId = 1;
        $db = new RelatedCategory();
        $data = $db->getRelCatArticles($catIds, $articleId);
        $this->assertEmpty($data);
    }
}
